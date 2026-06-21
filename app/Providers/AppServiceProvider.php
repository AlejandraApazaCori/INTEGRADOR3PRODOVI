<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   public function register()
{
    $this->app->singleton(FacebookService::class, function ($app) {
        return new FacebookService();
    });
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Compartir conteos de notificaciones con la barra superior
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager'])) {
                // Pagos pendientes fÃ­sicos NO VISTOS
                $countPagos = \App\Models\Pago::where('estado', 'pendiente')
                    ->where('metodo', 'fisico')
                    ->where('visto', false)
                    ->count();
                
                $latestPagos = \App\Models\Pago::with(['usuario', 'plan'])
                    ->where('estado', 'pendiente')
                    ->where('metodo', 'fisico')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->take(3)
                    ->get();

                // Tareas con archivos pendientes de revisiÃ³n NO VISTOS
                $countTareas = \App\Models\TareaArchivo::where('estado', 'pendiente')
                    ->where('visto', false)
                    ->count();

                $latestTareas = \App\Models\TareaArchivo::with(['tarea.campania', 'user'])
                    ->where('estado', 'pendiente')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->take(3)
                    ->get();

                $view->with([
                    'notificationCount' => $countPagos + $countTareas,
                    'pendingPaymentsCount' => $countPagos,
                    'pendingTasksCount' => $countTareas,
                    'latestPendingPayments' => $latestPagos,
                    'latestPendingTasks' => $latestTareas
                ]);
            }
        });
    }
}
