<?php

namespace App\Notifications;

use App\Models\Campania;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CampaniaCreadaNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Campania $campania) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campania->id,
            'title' => 'Tu campaña ya está activa',
            'message' => 'La campaña "'.$this->campania->nombre.'" fue creada y ya puedes verla en tu dashboard.',
            'icon' => 'fa-bullhorn',
        ];
    }
}
