<?php

namespace App\Console\Commands;

use App\Models\Tarea;
use App\Services\SocialPublicationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPublications extends Command
{
    protected $signature = 'publicaciones:procesar-programadas';

    protected $description = 'Publica automaticamente las tareas programadas en sus redes seleccionadas cuya hora ya llego';

    public function handle(SocialPublicationService $socialPublicationService): int
    {
        // El scheduler y el respaldo web ejecutan el mismo comando: comparten exclusión.
        $result = Cache::lock('publicaciones:procesar-programadas', 600)
            ->get(fn () => $this->process($socialPublicationService));

        return $result === false ? self::SUCCESS : $result;
    }

    private function process(SocialPublicationService $socialPublicationService): int
    {
        $tareas = Tarea::with([
            'archivos' => fn ($query) => $query->where('estado', 'aprobado'),
            'campania.cliente.socialAccounts',
            'campania.suscripcion.empresa.socialAccounts',
        ])
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
                $platforms = $tarea->publication_platforms ?: ['facebook'];
                $result = $socialPublicationService->publish($cliente, $tarea, $message, $platforms);
                $published = $result['success'] || $result['partial'];

                $tarea->forceFill([
                    'estado' => $published ? 'publicado' : $tarea->estado,
                    'publication_status' => $result['success'] ? 'published' : ($result['partial'] ? 'partial' : 'failed'),
                    'published_at' => $published ? now() : null,
                    'facebook_post_id' => $result['facebook_post_id'] ?? null,
                    'instagram_media_id' => $result['instagram_media_id'] ?? null,
                    'publication_error' => $result['success'] ? null : ($result['error'] ?? 'Error desconocido'),
                ])->save();

                if (! $result['success']) {
                    Log::warning('No se pudo completar una tarea programada en todas sus redes.', [
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
