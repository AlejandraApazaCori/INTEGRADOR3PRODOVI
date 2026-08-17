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
                $pagosNoVistos = \App\Models\Pago::with(['usuario', 'plan', 'codigoPago'])
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

                $notificationCount = \App\Models\Pago::where('visto', false)->count()
                    + \App\Models\Campania::where('visto', false)->count()
                    + \App\Models\TareaArchivo::where('visto', false)->count();

                $pagosVistos = \App\Models\Pago::with(['usuario', 'plan', 'codigoPago'])
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

                $dashboardNotifications = collect();

                foreach ($pagosNoVistos as $pago) {
                    $isPhysicalCode = $pago->metodo === 'fisico' && $pago->estado === 'pendiente';
                    $dashboardNotifications->push([
                        'type' => $isPhysicalCode ? 'physical-code' : 'payment-complete',
                        'icon' => $isPhysicalCode ? 'fa-receipt' : 'fa-circle-check',
                        'title' => $pago->usuario->name ?? 'Usuario',
                        'message' => $isPhysicalCode
                            ? 'Generó un código de pago físico para '.($pago->plan->nombre ?? '—').($pago->codigoPago ? ' · '.$pago->codigoPago->codigo : '')
                            : 'Realizó un pago para el plan '.($pago->plan->nombre ?? '—'),
                        'date' => $pago->created_at,
                        'url' => $isPhysicalCode
                            ? route('administrador.pagos.pendientes-fisicos')
                            : route('administrador.pagos.realizados'),
                    ]);
                }

                foreach ($campaniasNoVistas as $campania) {
                    $dashboardNotifications->push([
                        'type' => 'campaign',
                        'icon' => 'fa-bullhorn',
                        'title' => 'Nueva campaña',
                        'message' => $campania->nombre,
                        'date' => $campania->created_at,
                        'url' => route('administrador.campañas.show', $campania->id),
                    ]);
                }

                foreach ($tareasNoVistas as $archivo) {
                    $dashboardNotifications->push([
                        'type' => 'task',
                        'icon' => 'fa-paperclip',
                        'title' => $archivo->user->name ?? 'Usuario',
                        'message' => 'Subió un archivo a '.($archivo->tarea->nombre ?? 'una tarea'),
                        'date' => $archivo->created_at,
                        'url' => route('administrador.tareas.show', $archivo->tarea_id),
                    ]);
                }

                $dashboardNotifications = $dashboardNotifications->sortByDesc('date')->take(4);

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
                    'dashboardNotifications' => $dashboardNotifications,
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
