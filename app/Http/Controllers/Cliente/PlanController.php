<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Suscripcion;
use App\Models\Plan;
use App\Models\PlanCaracteristica;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    /**
     * Obtiene los datos del plan contratado por el usuario actual
     */
    public function getPlanContratado()
    {
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();

        // Obtener la suscripción activa del usuario con todas las relaciones necesarias
        $suscripcion = Suscripcion::with([
                'plan.planCaracteristicas' => function($query) {
                    $query->orderBy('orden');
                },
                'plan.planCaracteristicas.caracteristica'
            ])
            ->where('usuario_id', $userId)
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->latest('id')
            ->first();

        if (!$suscripcion) {
            // Si no hay suscripción activa, buscar la última suscripción (finalizada o cancelada)
            $suscripcion = Suscripcion::with([
                    'plan.planCaracteristicas' => function($query) {
                        $query->orderBy('orden');
                    },
                    'plan.planCaracteristicas.caracteristica'
                ])
                ->where('usuario_id', $userId)
                ->orderBy('fecha_inicio', 'desc')
                ->first();
        }

        if (!$suscripcion) {
            return response()->json([
                'error' => 'No se encontró ningún plan contratado'
            ], 404);
        }

        // Obtener todas las características del plan ordenadas
        $todasCaracteristicas = $suscripcion->plan->planCaracteristicas
            ->map(function ($pc) {
                return [
                    'nombre' => $pc->caracteristica->nombre,
                    'cantidad' => $pc->cantidad,
                    'frecuencia' => $pc->frecuencia,
                    'unidad' => $this->getUnidad($pc->caracteristica->nombre),
                    'es_destacado' => $pc->es_destacado
                ];
            });

        // Obtener características destacadas (para el dashboard)
        $caracteristicasDestacadas = $todasCaracteristicas
            ->where('es_destacado', true)
            ->take(3);

        $vigenciaActivada = $suscripcion->vigencia_activada_at !== null;
        $pagoConfirmado = $suscripcion->pagos()
            ->where('estado', 'completado')
            ->latest('fecha_pago')
            ->first();

        return response()->json([
            'plan' => [
                'nombre' => $suscripcion->plan->nombre,
                'descripcion' => $suscripcion->plan->descripcion,
                'vigencia_activada' => $vigenciaActivada,
                'fecha_inicio' => $vigenciaActivada ? Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') : null,
                'fecha_fin' => $vigenciaActivada ? Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') : null,
                'fecha_pago' => $pagoConfirmado?->fecha_pago?->format('d/m/Y'),
                'estado' => $suscripcion->estado,
                'caracteristicas' => $caracteristicasDestacadas,
                'todas_caracteristicas' => $todasCaracteristicas
            ]
        ]);
    }

  
    private function getUnidad($nombreCaracteristica)
    {
        $nombre = strtolower($nombreCaracteristica);
        
        if (str_contains($nombre, 'publicacion')) {
            return '/mes';
        } elseif (str_contains($nombre, 'red')) {
            return ' plataformas';
        } elseif (str_contains($nombre, 'diseño')) {
            return ' diseños';
        }
        return '';
    }
}
