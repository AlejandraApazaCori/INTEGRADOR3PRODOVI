<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\User;
use App\Notifications\TareaEntregadaNotification;
use App\Services\GoogleDriveReportService;
use App\Services\TaskReviewWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        $uploadFailed = false;
        if ($request->hasFile('archivos')) {
            try {
                $drive = app(GoogleDriveReportService::class);
            } catch (\Throwable $exception) {
                Log::error('No se pudo conectar con Google Drive.', ['error' => $exception->getMessage()]);
                return back()->with('error', 'No se pudo conectar con Google Drive.');
            }
            $tarea->loadMissing('campania.cliente');
            $campaign = $tarea->campania;
            if (! $campaign || ! $campaign->cliente) {
                return back()->with('error', 'La campaña debe tener un cliente para guardar entregables en Drive.');
            }

            try {
                $folderId = $drive->ensureTaskFolder(
                    $campaign->cliente->name,
                    $campaign->cliente->id,
                    $campaign->nombre,
                    $campaign->id,
                    $tarea->titulo,
                    $tarea->id,
                );
            } catch (\Throwable $exception) {
                Log::error('No se pudo preparar la carpeta del entregable en Drive.', ['tarea_id' => $tarea->id, 'error' => $exception->getMessage()]);
                return back()->with('error', 'No se pudo preparar la carpeta del entregable en Google Drive.');
            }

            foreach ($request->file('archivos') as $archivo) {
                try {
                    $driveId = $drive->uploadTaskFile(
                        $archivo->getRealPath(),
                        $archivo->getClientOriginalName(),
                        $archivo->getMimeType() ?: 'application/octet-stream',
                        $folderId,
                    );
                } catch (\Throwable $exception) {
                    Log::error('No se pudo subir el entregable a Drive.', ['tarea_id' => $tarea->id, 'error' => $exception->getMessage()]);
                    $uploadFailed = true;
                    break;
                }

                TareaArchivo::create([
                    'tarea_id' => $tarea->id,
                    'user_id' => Auth::id(),
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta_archivo' => 'drive:'.$driveId,
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

        if ($uploadFailed) {
            return back()->with('error', $archivosGuardados > 0
                ? 'Algunos archivos se guardaron, pero no se pudo subir uno de ellos a Google Drive.'
                : 'No se pudo subir el archivo a Google Drive.');
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

    public function media(Request $request, TareaArchivo $archivo, GoogleDriveReportService $drive)
    {
        abort_unless($archivo->isOnDrive(), 404);

        try {
            $stream = $drive->taskFileContent($archivo->driveFileId());
        } catch (\Throwable $exception) {
            Log::error('No se pudo leer el entregable de Drive.', ['archivo_id' => $archivo->id, 'error' => $exception->getMessage()]);
            abort(502, 'No se pudo leer el archivo de Google Drive.');
        }

        $format = $request->query('format');
        if (in_array($format, ['instagram-feed', 'instagram-story'], true)) {
            abort_unless(str_starts_with((string) $archivo->mime_type, 'image/'), 422);
            return $this->instagramImageResponse($stream, $format === 'instagram-feed');
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $safeName = str_replace(['"', "\r", "\n"], '', $archivo->nombre_original);

        return response()->stream(function () use ($stream) {
            while (! $stream->eof()) {
                echo $stream->read(1024 * 1024);
            }
        }, 200, [
            'Content-Type' => $archivo->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$safeName.'"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function instagramImageResponse(\Psr\Http\Message\StreamInterface $stream, bool $feed)
    {
        abort_unless(extension_loaded('gd'), 503, 'La conversión de imágenes no está disponible.');
        $source = @imagecreatefromstring((string) $stream);
        abort_unless($source, 422, 'La imagen no se pudo convertir.');

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $canvasWidth = $sourceWidth;
        $canvasHeight = $sourceHeight;
        if ($feed) {
            $ratio = $sourceWidth / $sourceHeight;
            if ($ratio > 1.91) {
                $canvasHeight = (int) ceil($sourceWidth / 1.91);
            } elseif ($ratio < 0.8) {
                $canvasWidth = (int) ceil($sourceHeight * 0.8);
            }
        }

        $scale = 1.0;
        if ($feed && $canvasWidth < 320) {
            $scale = 320 / $canvasWidth;
        } elseif ($feed && $canvasWidth > 1440) {
            $scale = 1440 / $canvasWidth;
        }
        $targetWidth = (int) round($canvasWidth * $scale);
        $targetHeight = (int) round($canvasHeight * $scale);
        $renderedWidth = (int) round($sourceWidth * $scale);
        $renderedHeight = (int) round($sourceHeight * $scale);
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled(
            $canvas, $source,
            (int) floor(($targetWidth - $renderedWidth) / 2),
            (int) floor(($targetHeight - $renderedHeight) / 2),
            0, 0, $renderedWidth, $renderedHeight, $sourceWidth, $sourceHeight
        );
        ob_start();
        imagejpeg($canvas, null, 92);
        $jpeg = ob_get_clean();
        imagedestroy($source);
        imagedestroy($canvas);

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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

    public function updateEstado(Request $request, TareaArchivo $archivo, TaskReviewWorkflow $review)
    {
        abort_unless(Auth::user()?->hasAnyRole(['Super Administrador', 'Administrador'])
            || (! Auth::user()?->hasAnyRole(['Diseñador', 'Disenador'])
                && (int) $archivo->tarea->asignado_id === (int) Auth::id()), 403, 'Solo la persona asignada o un administrador puede revisar este entregable.');
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,aprobado,rechazado'
        ]);

        $taskStatus = match ($validated['estado']) {
            'aprobado' => $archivo->tarea->revision_cliente_habilitada ? 'entregado' : 'aprobado',
            'rechazado' => 'reformular',
            default => 'pendiente',
        };

        $previousState = $archivo->estado;
        if ($previousState === $validated['estado']) {
            return back()->with('success', 'El entregable ya tiene ese estado.');
        }
        DB::transaction(function () use ($archivo, $validated, $taskStatus) {
            $archivo->update([
                'estado' => $validated['estado'],
                'revisado_equipo_por' => $validated['estado'] === 'pendiente' ? null : Auth::id(),
                'revisado_equipo_at' => $validated['estado'] === 'pendiente' ? null : now(),
                'revision_cliente_estado' => 'pendiente',
                'revisado_cliente_por' => null,
                'revisado_cliente_at' => null,
            ]);
            $archivo->tarea->update(['estado' => $taskStatus]);
        });

        if ($validated['estado'] === 'aprobado' && $previousState !== 'aprobado') {
            $review->notifyClient($archivo->fresh('tarea.campania.cliente'));
        }

        return back()->with('success', 'Estado del archivo y de la tarea actualizado correctamente');
    }

    public function updateClientReview(Request $request, Tarea $tarea, TaskReviewWorkflow $review)
    {
        abort_unless(Auth::user()?->hasAnyRole(['Super Administrador', 'Administrador'])
            || (! Auth::user()?->hasAnyRole(['Diseñador', 'Disenador'])
                && (int) $tarea->asignado_id === (int) Auth::id()), 403);
        $validated = $request->validate(['revision_cliente_habilitada' => ['required', 'boolean']]);
        $enabled = (bool) $validated['revision_cliente_habilitada'];
        abort_if(in_array($tarea->publication_status, ['published', 'partial'], true) || $tarea->estado === 'publicado', 409, 'Una publicación realizada no puede volver a revisión.');
        if ($enabled) {
            abort_unless($review->clientCompany($tarea) && $tarea->campania?->cliente, 422, 'La campaña necesita una empresa y un cliente asociados para activar esta revisión.');
        }
        $wasEnabled = (bool) $tarea->revision_cliente_habilitada;
        $tarea->update(['revision_cliente_habilitada' => $enabled]);

        if ($enabled && ! $wasEnabled) {
            if ($tarea->publication_status === 'scheduled') {
                $tarea->update([
                    'publication_status' => 'failed',
                    'publication_error' => 'Se activó la revisión con el cliente. Programa de nuevo cuando apruebe la pieza.',
                ]);
            }
            $tarea->archivos()->where('estado', 'aprobado')->update([
                'revision_cliente_estado' => 'pendiente',
                'revisado_cliente_por' => null,
                'revisado_cliente_at' => null,
            ]);
            if ($tarea->archivos()->where('estado', 'aprobado')->exists()) {
                $tarea->update(['estado' => 'entregado']);
            }
            $tarea->archivos()->where('estado', 'aprobado')->get()->each(fn ($file) => $review->notifyClient($file->load('tarea.campania.cliente')));
        } elseif (! $enabled && $wasEnabled && $tarea->archivos()->where('estado', 'aprobado')->exists()) {
            $tarea->update(['estado' => 'aprobado']);
        }

        return back()->with('success', $enabled ? 'La revisión con el cliente está activa.' : 'La revisión con el cliente está desactivada.');
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
        'archivos.user',
        'archivos.revisorEquipo',
        'archivos.revisorCliente',
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
