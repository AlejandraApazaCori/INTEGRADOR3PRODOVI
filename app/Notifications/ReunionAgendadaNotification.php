<?php

namespace App\Notifications;

use App\Models\Reunion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReunionAgendadaNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Reunion $reunion) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'campaign_id' => $this->reunion->campania_id,
            'meeting_id' => $this->reunion->id,
            'title' => 'Nueva reunión agendada',
            'message' => 'La reunión "'.$this->reunion->titulo.'" será el '.$this->reunion->fecha_inicio->format('d/m/Y').' a las '.$this->reunion->fecha_inicio->format('H:i').'.',
            'icon' => 'fa-calendar-check',
        ];
    }
}
