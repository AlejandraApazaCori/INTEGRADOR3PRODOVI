<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Schema;

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

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager'])) {

                // ── NO VISTAS (generan el badge rojo) ──────────────────────────
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

                // ── YA VISTAS (sección inferior del dropdown) ──────────────────
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
                    'notificationCount'    => $notificationCount,
                    'pagosNoVistos'        => $pagosNoVistos,
                    'campaniasNoVistas'    => $campaniasNoVistas,
                    'tareasNoVistas'       => $tareasNoVistas,
                    'pagosVistos'          => $pagosVistos,
                    'campaniasVistas'      => $campaniasVistas,
                    'tareasVistas'         => $tareasVistas,
                    // compatibilidad con código anterior
                    'latestPendingPayments' => $pagosNoVistos,
                    'latestPendingTasks'    => $tareasNoVistas,
                    'pendingPaymentsCount'  => $pagosNoVistos->count(),
                    'pendingTasksCount'     => $tareasNoVistas->count(),
                ]);
            }
        });
    }
}
