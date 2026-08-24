<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaComentario;
use App\Models\ComentarioArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TareaComentarioController extends Controller
{
    public function store(Request $request, Tarea $tarea)
    {
        $request->validate([
            'contenido' => 'required|string',
            'archivos.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo
        ]);

        // Crear el comentario
        $comentario = $tarea->comentarios()->create([
            'user_id' => Auth::id(),
            'contenido' => $request->contenido,
        ]);

        // Manejar archivos adjuntos al comentario
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $rutaArchivo = $archivo->store('comentarios/archivos', 'public');

                ComentarioArchivo::create([
                    'comentario_id' => $comentario->id,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta_archivo' => $rutaArchivo,
                    'extension' => $archivo->getClientOriginalExtension(),
                    'mime_type' => $archivo->getMimeType(),
                    'tamanio' => $archivo->getSize(),
                ]);
            }
        }

        return back()->with('success', 'Comentario agregado correctamente');
    }

    public function destroy(TareaComentario $comentario)
    {
        
        // Eliminar archivos asociados
        foreach ($comentario->archivos as $archivo) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
            $archivo->delete();
        }

        $comentario->delete();

        return back()->with('success', 'Comentario eliminado correctamente');
    }
}
