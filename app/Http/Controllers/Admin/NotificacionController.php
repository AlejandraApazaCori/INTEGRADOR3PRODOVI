<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\TareaArchivo;
use App\Models\Campania;
use App\Notifications\TareaEntregadaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function historial()
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager', 'Disenador', 'Diseñador'])->exists()) {
            abort(403);
        }
        $canSeeGlobalNotifications = $user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager']);

        $pagos = $canSeeGlobalNotifications ? Pago::with(['usuario', 'plan'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($p) => [
                'tipo'    => 'pago',
                'icono'   => '💰',
                'titulo'  => 'Nuevo pago',
                'detalle' => ($p->usuario->name ?? 'Usuario eliminado') . ' realizó un pago — Plan ' . ($p->plan->nombre ?? '—'),
                'fecha'   => $p->created_at,
                'visto'   => $p->visto,
                'url'     => url('administrador/pagos/pendientes-fisicos'),
            ]) : collect();

        $campanias = $canSeeGlobalNotifications ? Campania::with(['creador', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($c) => [
                'tipo'    => 'campaña',
                'icono'   => '📢',
                'titulo'  => 'Nueva campaña creada',
                'detalle' => 'Campaña "' . $c->nombre . '" creada' . ($c->cliente ? ' para ' . $c->cliente->name : ''),
                'fecha'   => $c->created_at,
                'visto'   => $c->visto,
                'url'     => route('administrador.campañas.show', $c->id),
            ]) : collect();

        $tareas = $user->notifications()
            ->where('type', TareaEntregadaNotification::class)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($notification) => [
                'tipo'    => 'tarea',
                'icono'   => '📁',
                'titulo'  => $notification->data['title'] ?? 'Tarea entregada',
                'detalle' => $notification->data['message'] ?? 'Se adjuntaron archivos a una tarea.',
                'fecha'   => $notification->created_at,
                'visto'   => $notification->read_at !== null,
                'url'     => route('administrador.tareas.show', $notification->data['task_id']),
            ]);

        $notificaciones = $pagos->concat($campanias)->concat($tareas)
            ->sortByDesc('fecha')
            ->values();

        return view('administrador.notificaciones.historial', compact('notificaciones'));
    }

    public function marcarVistas(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager', 'Disenador', 'Diseñador'])->exists()) {
            return response()->json(['ok' => false], 403);
        }

        if ($user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager'])) {
            Pago::where('visto', false)->update(['visto' => true]);
            Campania::where('visto', false)->update(['visto' => true]);
            TareaArchivo::where('visto', false)->update(['visto' => true]);
        }
        $user->unreadNotifications()->where('type', TareaEntregadaNotification::class)->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function conteo()
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager', 'Disenador', 'Diseñador'])->exists()) {
            return response()->json(['count' => 0]);
        }

        $canSeeGlobalNotifications = $user->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager']);
        $count = ($canSeeGlobalNotifications ? Pago::where('visto', false)->count() : 0)
            + ($canSeeGlobalNotifications ? Campania::where('visto', false)->count() : 0)
            + $user->unreadNotifications()->where('type', TareaEntregadaNotification::class)->count();

        return response()->json(['count' => $count]);
    }
}
