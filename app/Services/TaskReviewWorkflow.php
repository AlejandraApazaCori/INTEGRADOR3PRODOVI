<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\Empresa;
use App\Notifications\TaskReviewNotification;
use Illuminate\Support\Facades\Schema;

class TaskReviewWorkflow
{
    public function canPublish(Tarea $task): bool
    {
        if (! $task->revision_cliente_habilitada) {
            return $this->publishableFiles($task)->exists();
        }

        return $task->estado === 'aprobado'
            && $this->publishableFiles($task)->exists()
            && ! $task->archivos()->where('estado', 'aprobado')->where('revision_cliente_estado', 'pendiente')->exists();
    }

    public function publishableFiles(Tarea $task)
    {
        return $task->archivos()
            ->where('estado', 'aprobado')
            ->when($task->revision_cliente_habilitada, fn ($query) => $query->where('revision_cliente_estado', 'aprobado'));
    }

    public function needsClientReview(TareaArchivo $file): bool
    {
        return $file->tarea->revision_cliente_habilitada
            && $file->estado === 'aprobado'
            && $file->revision_cliente_estado === 'pendiente';
    }

    public function clientCompany(Tarea $task): ?Empresa
    {
        $task->loadMissing('campania.suscripcion.empresa', 'campania.empresas');
        $campaign = $task->campania;
        $company = $campaign?->suscripcion?->empresa ?? $campaign?->empresas->first();

        return $company && (int) $company->usuario_id === (int) $campaign->usuario_cliente_id ? $company : null;
    }

    public function notifyClient(TareaArchivo $file): void
    {
        if (! Schema::hasTable('notifications') || ! $this->needsClientReview($file)) {
            return;
        }

        $task = $file->tarea;
        $company = $this->clientCompany($task);
        $client = $task->campania?->cliente;
        if (! $company || ! $client) {
            return;
        }

        $client->notify(new TaskReviewNotification([
            'kind' => 'client_review_requested',
            'task_id' => $task->id,
            'file_id' => $file->id,
            'campaign_id' => $task->campania_id,
            'company_id' => $company->id,
            'title' => 'Publicación pendiente de tu revisión',
            'message' => 'Revisa “'.$task->titulo.'” y aprueba la pieza o solicita cambios.',
            'icon' => 'fa-clipboard-check',
        ]));
    }

    public function notifyAssignee(TareaArchivo $file, ?int $commentId, string $decision): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $task = $file->tarea;
        $assignee = $task->asignado;
        if (! $assignee) {
            return;
        }

        $assignee->notify(new TaskReviewNotification([
            'kind' => 'client_review_response',
            'task_id' => $task->id,
            'file_id' => $file->id,
            'comment_id' => $commentId,
            'campaign_id' => $task->campania_id,
            'url' => route('administrador.tareas.ver-subidas', $task).($commentId ? '#comentario-'.$commentId : '#revision-archivo-'.$file->id),
            'title' => 'Respuesta del cliente',
            'message' => 'El cliente '.($decision === 'aprobado' ? 'aprobó' : ($decision === 'rechazado' ? 'solicitó cambios en' : 'comentó')).' “'.$task->titulo.'”.',
            'icon' => 'fa-comment-dots',
        ]));
    }
}
