<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    /**
     * Obtiene los planes activos del usuario y conserva el plan principal
     * para los consumidores antiguos del endpoint.
     */
    public function getPlanContratado()
    {
        $relations = [
            'plan.planCaracteristicas' => function ($query) {
                $query->orderBy('orden');
            },
            'plan.planCaracteristicas.caracteristica',
            'empresa',
        ];

        $suscripcionesActivas = Suscripcion::with($relations)
            ->where('usuario_id', Auth::id())
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->latest('id')
            ->get();

        $suscripcionPrincipal = $suscripcionesActivas->first();

        if (! $suscripcionPrincipal) {
            $suscripcionPrincipal = Suscripcion::with($relations)
                ->where('usuario_id', Auth::id())
                ->latest('fecha_inicio')
                ->first();
        }

        if (! $suscripcionPrincipal) {
            return response()->json([
                'error' => 'No se encontró ningún plan contratado',
            ], 404);
        }

        return response()->json([
            'plan' => $this->formatSubscription($suscripcionPrincipal),
            'plans' => $suscripcionesActivas
                ->map(fn (Suscripcion $suscripcion) => $this->formatSubscription($suscripcion))
                ->values(),
        ]);
    }

    private function formatSubscription(Suscripcion $suscripcion): array
    {
        $todasCaracteristicas = $suscripcion->plan->planCaracteristicas
            ->map(function ($planCaracteristica) {
                return [
                    'nombre' => $planCaracteristica->caracteristica->nombre,
                    'cantidad' => $planCaracteristica->cantidad,
                    'frecuencia' => $planCaracteristica->frecuencia,
                    'unidad' => $this->getUnidad($planCaracteristica->caracteristica->nombre),
                    'es_destacado' => $planCaracteristica->es_destacado,
                ];
            })
            ->values();

        $vigenciaActivada = $suscripcion->vigencia_activada_at !== null;
        $pagoConfirmado = $suscripcion->pagos()
            ->where('estado', 'completado')
            ->latest('fecha_pago')
            ->first();

        return [
            'id' => $suscripcion->id,
            'nombre' => $suscripcion->plan->nombre,
            'descripcion' => $suscripcion->plan->descripcion,
            'empresa' => $suscripcion->empresa?->nombre_empresa,
            'empresa_id' => $suscripcion->empresa?->id,
            'vigencia_activada' => $vigenciaActivada,
            'fecha_inicio' => $vigenciaActivada ? Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') : null,
            'fecha_fin' => $vigenciaActivada ? Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') : null,
            'fecha_pago' => $pagoConfirmado?->fecha_pago?->format('d/m/Y'),
            'estado' => $suscripcion->estado,
            'caracteristicas' => $todasCaracteristicas
                ->where('es_destacado', true)
                ->take(3)
                ->values(),
            'todas_caracteristicas' => $todasCaracteristicas,
        ];
    }

    private function getUnidad(string $nombreCaracteristica): string
    {
        $nombre = strtolower($nombreCaracteristica);

        if (str_contains($nombre, 'publicacion')) {
            return '/mes';
        }

        if (str_contains($nombre, 'red')) {
            return ' plataformas';
        }

        if (str_contains($nombre, 'diseño')) {
            return ' diseños';
        }

        return '';
    }
}
