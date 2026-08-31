<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClienteTareaRevisionController extends Controller
{
    public function store(Request $request, TareaArchivo $archivo): RedirectResponse
    {
        $archivo->loadMissing('tarea.campania');
        $tarea = $archivo->tarea;

        $this->authorizeClientTask($tarea);

        $validated = $request->validate([
            'estado' => ['nullable', Rule::in(['aprobado', 'rechazado'])],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_if(blank($validated['estado'] ?? null) && blank($validated['comentario'] ?? null), 422, 'Debes seleccionar una acción o escribir un comentario.');

        if (filled($validated['estado'] ?? null)) {
            $archivo->update(['estado' => $validated['estado']]);
        }

        if (filled($validated['comentario'] ?? null)) {
            $tarea->comentarios()->create([
                'user_id' => Auth::id(),
                'contenido' => trim($validated['comentario']),
            ]);
        }

        $message = match ($validated['estado'] ?? null) {
            'aprobado' => 'La pieza fue aprobada correctamente.',
            'rechazado' => 'Se solicitaron cambios para la pieza.',
            default => 'Tu comentario fue enviado al equipo.',
        };

        return back()->with('dashboard_review_success', $message);
    }

    private function authorizeClientTask(Tarea $tarea): void
    {
        abort_unless(
            $tarea->visible_cliente
            && (int) $tarea->campania?->usuario_cliente_id === (int) Auth::id(),
            403
        );
    }
}
