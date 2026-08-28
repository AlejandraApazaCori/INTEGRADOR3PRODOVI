<?php

namespace App\Services;

use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use RuntimeException;

class CampaignPreparationService
{
    public function __construct(
        private readonly GroqService $summaryService,
        private readonly MarketingPlanService $marketingPlanService,
    ) {}

    public function prepare(Suscripcion $suscripcion): PlanMarketing
    {
        $suscripcion->loadMissing([
            'empresa.respuestasCuestionario.pregunta',
            'plan.planCaracteristicas.caracteristica',
        ]);

        $empresa = $suscripcion->empresa;

        if (! $empresa) {
            throw new RuntimeException('El cliente todavía no tiene una empresa registrada.');
        }

        if (! $empresa->cuestionario_completado || $empresa->respuestasCuestionario->isEmpty()) {
            throw new RuntimeException('El cuestionario empresarial debe completarse antes de crear la campaña.');
        }

        if (blank($empresa->resumen_ejecutivo)) {
            $respuestas = $empresa->respuestasCuestionario->map(fn ($respuesta) => [
                'pregunta' => $respuesta->pregunta?->pregunta ?? 'Pregunta no disponible',
                'respuesta' => $respuesta->respuesta,
            ])->all();

            $resumen = $this->summaryService->generateSummary($empresa->nombre_empresa, $respuestas);

            if (blank($resumen) || str_contains(mb_strtolower($resumen), 'hubo un error')) {
                throw new RuntimeException('No fue posible generar el resumen ejecutivo. Verifica la conexión con la IA e inténtalo nuevamente.');
            }

            $empresa->update(['resumen_ejecutivo' => $resumen]);
        }

        $planExistente = PlanMarketing::query()
            ->where('empresa_id', $empresa->id)
            ->where('suscripcion_id', $suscripcion->id)
            ->where('estado', 'activo')
            ->latest()
            ->first();

        if ($planExistente) {
            return $planExistente;
        }

        $caracteristicas = $suscripcion->plan?->planCaracteristicas
            ?->sortBy('orden')
            ->values()
            ->map(function ($item) {
                $nombre = (string) ($item->caracteristica?->nombre ?? '');

                return [
                    'nombre' => $nombre,
                    'cantidad' => $item->cantidad,
                    'unidad' => $this->inferirUnidad($nombre),
                    'frecuencia' => $item->frecuencia,
                    'descripcion' => $item->caracteristica?->descripcion,
                    'orden' => $item->orden,
                    'es_destacado' => (bool) $item->es_destacado,
                ];
            })->all() ?? [];

        $planContexto = [
            'nombre' => $suscripcion->plan?->nombre,
            'descripcion' => $suscripcion->plan?->descripcion,
            'periodo_facturacion' => $suscripcion->plan?->periodo_facturacion,
            'precio' => $suscripcion->plan?->precio,
            'moneda' => $suscripcion->plan?->moneda,
        ];

        $contenido = $this->marketingPlanService->generateMarketingPlan(
            $empresa->nombre_empresa,
            $empresa->resumen_ejecutivo,
            $caracteristicas,
            $planContexto,
        );

        if (blank($contenido) || str_contains(mb_strtolower($contenido), 'hubo un error')) {
            throw new RuntimeException('El resumen fue generado, pero no fue posible crear el plan de marketing. Inténtalo nuevamente.');
        }

        return PlanMarketing::create([
            'empresa_id' => $empresa->id,
            'suscripcion_id' => $suscripcion->id,
            'contenido' => $contenido,
            'estado' => 'activo',
        ]);
    }

    private function inferirUnidad(string $nombre): ?string
    {
        $nombre = mb_strtolower(trim($nombre));

        return match (true) {
            str_contains($nombre, 'post') => 'posts',
            str_contains($nombre, 'diseño'), str_contains($nombre, 'diseno') => 'diseños',
            str_contains($nombre, 'video') => 'videos',
            str_contains($nombre, 'gif') => 'gifs',
            str_contains($nombre, 'foto') => 'sesiones',
            str_contains($nombre, 'catálogo'), str_contains($nombre, 'catalogo') => 'catálogos',
            default => null,
        };
    }
}
