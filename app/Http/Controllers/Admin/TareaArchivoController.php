<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TareaArchivoController extends Controller
{
    public function create(Tarea $tarea)
    {
        return view('administrador.tareas.subir', compact('tarea'));
    }

    public function store(Request $request, Tarea $tarea)
    {
        $request->validate([
            'archivos' => 'required',
            'archivos.*' => 'file|max:1000240', // Máximo 1000MB por archivo
            'descripcion' => 'nullable|string',
        ]);

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
            }
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
}
