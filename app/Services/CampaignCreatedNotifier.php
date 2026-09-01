<?php

namespace App\Services;

use App\Mail\CampaniaCreada;
use App\Models\Campania;
use App\Notifications\CampaniaCreadaNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class CampaignCreatedNotifier
{
    public function send(Campania $campania): void
    {
        $campania->loadMissing('cliente');
        $client = $campania->cliente;

        if (! $client || $campania->estado !== 'activa' || $campania->es_borrador) {
            return;
        }

        if (Schema::hasTable('notifications')) {
            try {
                $alreadyNotified = $client->notifications()
                    ->where('type', CampaniaCreadaNotification::class)
                    ->where('data->campaign_id', $campania->id)
                    ->exists();

                if ($alreadyNotified) {
                    return;
                }

                $client->notify(new CampaniaCreadaNotification($campania));
            } catch (\Throwable $exception) {
                Log::warning('No se pudo crear la notificación de campaña para el cliente.', [
                    'campania_id' => $campania->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            Mail::to($client->email)->send(new CampaniaCreada($client, $campania));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar el correo de campaña creada.', [
                'campania_id' => $campania->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
