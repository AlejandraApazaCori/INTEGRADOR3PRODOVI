<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TareaEntregadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Tarea $tarea,
        private readonly User $uploader,
        private readonly int $fileCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->tarea->id,
            'campaign_id' => $this->tarea->campania_id,
            'title' => 'Tarea entregada',
            'message' => $this->uploader->name.' subió '.$this->fileCount.' '.($this->fileCount === 1 ? 'archivo' : 'archivos').' a “'.$this->tarea->titulo.'”.',
            'icon' => 'fa-paperclip',
        ];
    }
}
