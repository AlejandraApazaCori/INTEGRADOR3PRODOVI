<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\TareaArchivo;
use App\Models\Campania;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function historial()
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager'])->exists()) {
            abort(403);
        }

        $pagos = Pago::with(['usuario', 'plan'])
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
            ]);

        $campanias = Campania::with(['creador', 'cliente'])
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
            ]);

        $tareas = TareaArchivo::with(['tarea.campania', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($a) => [
                'tipo'    => 'tarea',
                'icono'   => '📁',
                'titulo'  => 'Archivo subido a tarea',
                'detalle' => ($a->user->name ?? 'Usuario eliminado') . ' subió un archivo a: ' . ($a->tarea->nombre ?? '—'),
                'fecha'   => $a->created_at,
                'visto'   => $a->visto,
                'url'     => route('administrador.tareas.show', $a->tarea_id),
            ]);

        $notificaciones = $pagos->concat($campanias)->concat($tareas)
            ->sortByDesc('fecha')
            ->values();

        return view('administrador.notificaciones.historial', compact('notificaciones'));
    }

    public function marcarVistas(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager'])->exists()) {
            return response()->json(['ok' => false], 403);
        }

        Pago::where('visto', false)->update(['visto' => true]);
        Campania::where('visto', false)->update(['visto' => true]);
        TareaArchivo::where('visto', false)->update(['visto' => true]);

        return response()->json(['ok' => true]);
    }

    public function conteo()
    {
        $user = Auth::user();
        if (!$user || !$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador', 'Community Manager'])->exists()) {
            return response()->json(['count' => 0]);
        }

        $count = Pago::where('visto', false)->count()
            + Campania::where('visto', false)->count()
            + TareaArchivo::where('visto', false)->count();

        return response()->json(['count' => $count]);
    }
}
