<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Suscripcion;
use Illuminate\Http\Request;

class PlanIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $perPage = max(5, min((int) $request->input('per_page', 10), 100));
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $currency = $request->input('currency');
        $period = $request->input('period');
        $order = $request->input('order', 'position');

        $planSummary = [
            'total' => Plan::count(),
            'active' => Plan::where('activo', true)->count(),
            'inactive' => Plan::where('activo', false)->count(),
            'subscriptions' => Suscripcion::whereHas('plan')
                ->where('estado', 'activa')
                ->where(function ($query) {
                    $query->whereNull('vigencia_activada_at')
                        ->orWhere('fecha_fin', '>', now());
                })
                ->count(),
        ];

        $planes = Plan::with(['planCaracteristicas.caracteristica'])
            ->withCount(['suscripciones' => function ($query) {
                $query->where('estado', 'activa')
                    ->where(function ($activeQuery) {
                        $activeQuery->whereNull('vigencia_activada_at')
                            ->orWhere('fecha_fin', '>', now());
                    });
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('subtitulo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('activo', $status === 'active'))
            ->when(in_array($currency, ['BS', 'USD'], true), fn ($query) => $query->where('moneda', $currency))
            ->when(in_array($period, ['mes', 'trimestre', 'semestre', 'año'], true), fn ($query) => $query->where('periodo_facturacion', $period))
            ->when($order === 'price_asc', fn ($query) => $query->orderBy('precio'))
            ->when($order === 'price_desc', fn ($query) => $query->orderByDesc('precio'))
            ->when($order === 'name', fn ($query) => $query->orderBy('nombre'))
            ->when($order === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when(! in_array($order, ['price_asc', 'price_desc', 'name', 'newest'], true), fn ($query) => $query->orderBy('orden')->orderBy('nombre'))
            ->paginate($perPage)
            ->appends($request->query());

        return view('administrador.planes.listado', compact('planes', 'planSummary', 'perPage'));
    }
}
