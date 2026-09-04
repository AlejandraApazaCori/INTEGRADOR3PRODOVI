<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\User;
use App\Notifications\TareaEntregadaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TareaArchivoController extends Controller
{
    public function create(Tarea $tarea)
    {
        return redirect()->to(route('administrador.tareas.ver-subidas', $tarea).'?subir=1');
    }

    public function store(Request $request, Tarea $tarea)
    {
        $fromCampaign = $request->input('contexto') === 'campania' && $tarea->campania_id;
        $fromReview = $request->input('contexto') === 'revision';
        $validator = Validator::make($request->all(), [
            'archivos' => ['required', 'array', 'min:1'],
            'archivos.*' => ['file', 'max:1000240'], // Máximo aproximado de 1 GB por archivo
            'descripcion' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            if ($fromCampaign) {
                return redirect()->to(route('administrador.campañas.show', $tarea->campania_id).'#tareas')
                    ->withErrors($validator, 'taskUpload')
                    ->withInput()
                    ->with('upload_task_id', $tarea->id);
            }

            if ($fromReview) {
                return redirect()->route('administrador.tareas.ver-subidas', $tarea)
                    ->withErrors($validator, 'reviewUpload')
                    ->withInput()
                    ->with('open_upload_drawer', true);
            }

            return back()->withErrors($validator)->withInput();
        }

        $archivosGuardados = 0;
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $rutaArchivo = $archivo->store('tareas/archivos', 'public');

                TareaArchivo::create([
                    'tarea_id' => $tarea->id,
                    'user_id' => Auth::id(),
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta_archivo' => $rutaArchivo,
                    'extension' => $archivo->getClientOriginalExtension(),
                    'mime_type' => $archivo->getMimeType(),
                    'tamanio' => $archivo->getSize(),
                    'descripcion' => $request->descripcion,
                ]);
                $archivosGuardados++;
            }
        }

        if ($archivosGuardados > 0) {
            $tarea->update(['estado' => 'entregado']);
            $this->notifyInternalTeam($tarea, $archivosGuardados);
        }

        if ($fromCampaign) {
            return redirect()->to(route('administrador.campañas.show', $tarea->campania_id).'#tareas')
                ->with('success', 'Archivo(s) subido(s) correctamente');
        }

        if ($fromReview) {
            return redirect()->route('administrador.tareas.ver-subidas', $tarea)
                ->with('success', 'Archivo(s) subido(s) correctamente');
        }

        return redirect()->route('administrador.tareas.show', $tarea->id)
            ->with('success', 'Archivo(s) subido(s) correctamente');
    }
    public function show(Tarea $tarea)
    {
        // Marcar como vistos los archivos pendientes de esta tarea, 
        // EXCEPTO los que el propio usuario actual acaba de subir (para que otros admins reciban la noti)
        $tarea->archivos()
            ->where('estado', 'pendiente')
            ->where('user_id', '!=', Auth::id())
            ->update(['visto' => true]);

        // Cargar relaciones necesarias
        $tarea->load([
            'creador',
            'asignado',
            'archivos',
            'campania',
            'comentarios.user'
        ]);

        return view('administrador.tareas.show', compact('tarea'));
    }

    public function updateEstado(Request $request, TareaArchivo $archivo)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,aprobado,rechazado'
        ]);

        $archivo->update(['estado' => $request->estado]);

        if ($request->estado === 'aprobado') {
            $archivo->tarea()->update(['estado' => 'aprobado']);
        } elseif ($request->estado === 'rechazado') {
            $archivo->tarea()->update(['estado' => 'reformular']);
        }

        return back()->with('success', 'Estado del archivo actualizado correctamente');
    }
    public function verSubidas(Tarea $tarea)
{
    $tarea->archivos()
        ->where('estado', 'pendiente')
        ->where('user_id', '!=', Auth::id())
        ->update(['visto' => true]);

    // Cargar relaciones necesarias
    $tarea->load([
        'creador',
        'asignado',
        'archivos.user', // Asegúrate de cargar el usuario que subió cada archivo
        'campania',
        'comentarios.user',
        'comentarios.archivos',
    ]);

    return view('administrador.tareas.vertareassubidas', compact('tarea'));
}

    private function notifyInternalTeam(Tarea $tarea, int $fileCount): void
    {
        if (! Schema::hasTable('notifications') || ! Auth::user()) {
            return;
        }

        $tarea->loadMissing([
            'asignado',
            'responsables',
            'campania.creador',
            'campania.communityManager',
            'campania.disenador',
            'campania.disenadores',
        ]);

        $campaign = $tarea->campania;
        $clientId = (int) ($campaign?->usuario_cliente_id ?? 0);
        $recipients = collect([
            $tarea->asignado,
            $campaign?->creador,
            $campaign?->communityManager,
            $campaign?->disenador,
        ])
            ->merge($tarea->responsables)
            ->merge($campaign?->disenadores ?? collect())
            ->filter(fn ($recipient) => $recipient instanceof User)
            ->reject(fn (User $recipient) => (int) $recipient->id === $clientId)
            ->unique('id')
            ->values();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new TareaEntregadaNotification($tarea, Auth::user(), $fileCount));
        }
    }
}
