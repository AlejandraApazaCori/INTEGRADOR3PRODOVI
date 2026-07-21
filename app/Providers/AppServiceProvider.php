<?php

namespace App\Providers;

use App\Models\Tarea;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FacebookService::class, function ($app) {
            return new FacebookService();
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->processScheduledPublicationsFromWeb();

        View::composer('layouts.app', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager'])) {
                $pagosNoVistos = \App\Models\Pago::with(['usuario', 'plan'])
                    ->where('visto', false)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $campaniasNoVistas = \App\Models\Campania::with(['creador', 'cliente'])
                    ->where('visto', false)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $tareasNoVistas = \App\Models\TareaArchivo::with(['tarea', 'user'])
                    ->where('visto', false)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $notificationCount = $pagosNoVistos->count()
                    + $campaniasNoVistas->count()
                    + $tareasNoVistas->count();

                $pagosVistos = \App\Models\Pago::with(['usuario', 'plan'])
                    ->where('visto', true)
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

                $campaniasVistas = \App\Models\Campania::with(['creador', 'cliente'])
                    ->where('visto', true)
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

                $tareasVistas = \App\Models\TareaArchivo::with(['tarea', 'user'])
                    ->where('visto', true)
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

                $view->with([
                    'notificationCount' => $notificationCount,
                    'pagosNoVistos' => $pagosNoVistos,
                    'campaniasNoVistas' => $campaniasNoVistas,
                    'tareasNoVistas' => $tareasNoVistas,
                    'pagosVistos' => $pagosVistos,
                    'campaniasVistas' => $campaniasVistas,
                    'tareasVistas' => $tareasVistas,
                    'latestPendingPayments' => $pagosNoVistos,
                    'latestPendingTasks' => $tareasNoVistas,
                    'pendingPaymentsCount' => $pagosNoVistos->count(),
                    'pendingTasksCount' => $tareasNoVistas->count(),
                ]);
            }
        });
    }

    private function processScheduledPublicationsFromWeb(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (! Schema::hasTable('tareas')) {
                return;
            }

            if (! Schema::hasColumns('tareas', [
                'publication_status',
                'publication_scheduled_at',
                'publication_message',
            ])) {
                return;
            }

            $lockKey = 'publicaciones_programadas.web_runner';
            $shouldRunNow = Cache::add($lockKey, now()->timestamp, 55);

            if (! $shouldRunNow) {
                return;
            }

            $hasDuePublications = Tarea::query()
                ->where('publication_status', 'scheduled')
                ->whereNotNull('publication_scheduled_at')
                ->where('publication_scheduled_at', '<=', now())
                ->exists();

            if (! $hasDuePublications) {
                return;
            }

            Artisan::call('publicaciones:procesar-programadas');
        } catch (\Throwable $e) {
            Log::warning('No se pudo ejecutar el respaldo web de publicaciones programadas.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
