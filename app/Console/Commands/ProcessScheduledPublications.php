<?php

namespace App\Console\Commands;

use App\Models\Tarea;
use App\Services\FacebookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPublications extends Command
{
    protected $signature = 'publicaciones:procesar-programadas';

    protected $description = 'Publica automaticamente las tareas programadas de Facebook cuya hora ya llego';

    public function handle(FacebookService $facebookService): int
    {
        $tareas = Tarea::with(['campania.cliente.socialAccounts'])
            ->where('publication_status', 'scheduled')
            ->whereNotNull('publication_scheduled_at')
            ->where('publication_scheduled_at', '<=', now())
            ->orderBy('publication_scheduled_at')
            ->get();

        if ($tareas->isEmpty()) {
            $this->info('No hay publicaciones programadas pendientes.');
            return self::SUCCESS;
        }

        foreach ($tareas as $tarea) {
            $cliente = $tarea->campania?->cliente;
            $message = $tarea->publication_message;

            if (! $cliente || ! filled($message)) {
                $tarea->forceFill([
                    'publication_status' => 'failed',
                    'publication_error' => 'La tarea programada no tiene cliente o mensaje valido para publicar.',
                ])->save();

                continue;
            }

            try {
                $result = $facebookService->publishTaskForUser($cliente, $tarea, $message);

                $tarea->forceFill([
                    'publication_status' => $result['success'] ? 'published' : 'failed',
                    'published_at' => $result['success'] ? now() : null,
                    'facebook_post_id' => $result['facebook_post_id'] ?? null,
                    'publication_error' => $result['success'] ? null : ($result['error'] ?? 'Error desconocido'),
                ])->save();

                if (! $result['success']) {
                    Log::warning('No se pudo publicar una tarea programada de Facebook.', [
                        'tarea_id' => $tarea->id,
                        'error' => $result['error'] ?? 'Error desconocido',
                    ]);
                }
            } catch (\Throwable $e) {
                $tarea->forceFill([
                    'publication_status' => 'failed',
                    'publication_error' => $e->getMessage(),
                ])->save();

                Log::error('Fallo inesperado publicando tarea programada.', [
                    'tarea_id' => $tarea->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Publicaciones programadas procesadas correctamente.');

        return self::SUCCESS;
    }
}

