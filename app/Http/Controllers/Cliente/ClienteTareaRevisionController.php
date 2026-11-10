<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Services\TaskReviewWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteTareaRevisionController extends Controller
{
    public function store(Request $request, TareaArchivo $archivo, TaskReviewWorkflow $review): RedirectResponse
    {
        $archivo->loadMissing('tarea.campania');
        $tarea = $archivo->tarea;

        $this->authorizeClientTask($tarea, $review);
        abort_unless($archivo->estado === 'aprobado', 409, 'El equipo debe aprobar la pieza antes de enviarla al cliente.');

        $validated = $request->validate([
            'estado' => ['nullable', Rule::in(['aprobado', 'rechazado'])],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_if(blank($validated['estado'] ?? null) && blank($validated['comentario'] ?? null), 422, 'Debes seleccionar una acción o escribir un comentario.');

        $commentId = DB::transaction(function () use ($archivo, $tarea, $validated) {
            $lockedFile = TareaArchivo::query()->whereKey($archivo->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedFile->estado === 'aprobado' && $lockedFile->revision_cliente_estado === 'pendiente', 409, 'Esta pieza ya fue revisada.');
            $comment = null;
            if (filled($validated['comentario'] ?? null)) {
                $comment = $tarea->comentarios()->create([
                    'user_id' => Auth::id(),
                    'contenido' => trim($validated['comentario']),
                ]);
            }

            if (filled($validated['estado'] ?? null)) {
                $lockedFile->update([
                    'revision_cliente_estado' => $validated['estado'],
                    'revisado_cliente_por' => Auth::id(),
                    'revisado_cliente_at' => now(),
                ]);
                if ($validated['estado'] === 'aprobado') {
                    $hasPendingFiles = $tarea->archivos()
                        ->where('estado', 'aprobado')
                        ->where('revision_cliente_estado', 'pendiente')
                        ->exists();
                    $tarea->update(['estado' => $hasPendingFiles ? 'entregado' : 'aprobado']);
                } else {
                    $tarea->update(['estado' => 'reformular']);
                    if ($tarea->publication_status === 'scheduled') {
                        $tarea->update([
                            'publication_status' => 'failed',
                            'publication_error' => 'El cliente solicitó cambios. La publicación programada fue detenida.',
                        ]);
                    }
                }
            }

            return $comment?->id;
        });

        $archivo->refresh();
        $review->notifyAssignee($archivo, $commentId, $validated['estado'] ?? 'comentado');

        $message = match ($validated['estado'] ?? null) {
            'aprobado' => 'La pieza fue aprobada correctamente.',
            'rechazado' => 'Se solicitaron cambios para la pieza.',
            default => 'Tu comentario fue enviado al equipo.',
        };

        return back()->with('dashboard_review_success', $message);
    }

    private function authorizeClientTask(Tarea $tarea, TaskReviewWorkflow $review): void
    {
        $company = $review->clientCompany($tarea);
        abort_unless(
            $tarea->revision_cliente_habilitada
            && $company
            && (int) $company->usuario_id === (int) Auth::id()
            && (int) $tarea->campania?->usuario_cliente_id === (int) Auth::id(),
            403
        );
    }
}
