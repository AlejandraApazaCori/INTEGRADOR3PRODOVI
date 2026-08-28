<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Plan;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Services\CampaignBlueprintService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampaignBlueprintServiceTest extends TestCase
{
    public function test_it_preserves_the_exact_calendar_and_adds_only_useful_operational_tasks(): void
    {
        Carbon::setTestNow('2026-08-24 10:00:00');

        $company = new Empresa([
            'nombre_empresa' => 'Lumina',
            'descripcion' => 'Asesoria previsional',
            'resumen_ejecutivo' => 'La empresa busca captar nuevos clientes.',
        ]);
        $company->setRelation('respuestasCuestionario', collect());

        $contractedPlan = new Plan(['nombre' => 'Plan profesional']);
        $contractedPlan->setRelation('planCaracteristicas', collect());

        $subscription = new Suscripcion(['fecha_fin' => '2026-09-24']);
        $subscription->setRelation('empresa', $company);
        $subscription->setRelation('plan', $contractedPlan);

        $marketingPlan = new PlanMarketing([
            'contenido' => <<<'MARKDOWN'
## 10. Calendario Operativo Mensual
| Semana | Tema | Objetivo | CTA | Posts |
|---|---|---|---|---|
| 1 | Educacion | Informar | Escribenos | 1. **Lunes** - Post educativo. <br>2. **Miercoles** - Carrusel de beneficios. |
MARKDOWN,
        ]);

        $aiBlueprint = [
            'nombre' => 'Campana educativa Lumina',
            'descripcion' => 'Campana lista para ejecucion.',
            'objetivo_general' => 'Captar consultas calificadas.',
            'publicos_objetivo' => [
                [
                    'tipo_edades' => 'Adultos planificadores (35-55 años)',
                    'descripcion' => 'Buscan comprender sus aportes y asegurar su estabilidad financiera.',
                ],
                [
                    'tipo_edades' => 'Profesionales independientes (30-50 años)',
                    'descripcion' => 'Necesitan optimizar aportes y evitar errores en sus trámites.',
                ],
            ],
            'mensaje_principal' => 'Planifica hoy tu futuro.',
            'tono_comunicacion' => 'Profesional y cercano',
            'canales' => ['Facebook', 'Instagram', 'TikTok', 'WhatsApp'],
            'indicadores' => ['Alcance', 'Consultas'],
            'tareas' => [
                [
                    'titulo' => 'Post inventado por IA',
                    'descripcion' => 'Esta pieza no debe duplicar el calendario aprobado.',
                    'entregable' => 'Post',
                    'fecha_inicio' => '2026-08-24',
                    'fecha_limite' => '2026-08-25',
                    'prioridad' => 'alta',
                    'roles_sugeridos' => ['Diseñador', 'Community Manager'],
                    'tipo_contenido' => 'post',
                    'requiere_aprobacion' => true,
                    'visible_cliente' => true,
                ],
                [
                    'titulo' => 'Preparar informe final',
                    'descripcion' => str_repeat('Medir resultados y documentar hallazgos. ', 30),
                    'entregable' => 'Informe de rendimiento',
                    'fecha_inicio' => '2026-09-30',
                    'fecha_limite' => '2026-10-02',
                    'prioridad' => 'media',
                    'roles_sugeridos' => ['Community Manager', 'Administrador'],
                    'tipo_contenido' => 'otro',
                    'requiere_aprobacion' => false,
                    'visible_cliente' => true,
                ],
            ],
        ];

        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => json_encode($aiBlueprint)]]],
            ]),
        ]);

        $result = app(CampaignBlueprintService::class)->generate($subscription, $marketingPlan, 'asistido');

        $this->assertCount(3, $result['tareas']);
        $this->assertSame('post', $result['tareas'][0]['tipo_contenido']);
        $this->assertSame('carrusel', $result['tareas'][1]['tipo_contenido']);
        $this->assertSame('Preparar informe final', $result['tareas'][2]['titulo']);
        $this->assertSame('2026-09-24', $result['tareas'][2]['fecha_limite']);
        $this->assertLessThanOrEqual(700, mb_strlen($result['tareas'][2]['descripcion']));
        $this->assertSame(['Facebook', 'Instagram', 'TikTok', 'WhatsApp'], $result['canales']);
        $this->assertCount(2, $result['publicos_objetivo']);
        $this->assertSame('Adultos planificadores (35-55 años)', $result['publicos_objetivo'][0]['tipo_edades']);
        $this->assertNotContains('Post inventado por IA', array_column($result['tareas'], 'titulo'));

        Http::assertSent(fn ($request) => str_contains(
            (string) data_get($request->data(), 'messages.0.content'),
            'CUESTIONARIO EMPRESARIAL'
        ));

        Carbon::setTestNow();
    }

    public function test_it_prepares_a_complete_local_proposal_when_groq_is_unavailable(): void
    {
        Carbon::setTestNow('2026-08-24 10:00:00');

        $company = new Empresa([
            'nombre_empresa' => 'Lumina',
            'descripcion' => 'Asesoria previsional',
            'resumen_ejecutivo' => 'La empresa busca captar nuevos clientes.',
        ]);
        $company->setRelation('respuestasCuestionario', collect());
        $contractedPlan = new Plan(['nombre' => 'Plan profesional']);
        $contractedPlan->setRelation('planCaracteristicas', collect());
        $subscription = new Suscripcion(['fecha_fin' => '2026-09-24']);
        $subscription->setRelation('empresa', $company);
        $subscription->setRelation('plan', $contractedPlan);
        $marketingPlan = new PlanMarketing([
            'contenido' => 'Estrategia para Facebook e Instagram orientada a alcance, consultas y conversiones.',
        ]);

        Http::fake(['*' => Http::response(['error' => ['message' => 'rate limit']], 429)]);

        $result = app(CampaignBlueprintService::class)->generate($subscription, $marketingPlan, 'asistido');

        $this->assertSame('automatic_fallback', $result['generation_source']);
        $this->assertNull($result['generation_warning']);
        $this->assertCount(3, $result['tareas']);
        $this->assertSame(['Facebook', 'Instagram'], $result['canales']);
        $this->assertSame(['Community Manager', 'Administrador'], $result['tareas'][0]['roles_sugeridos']);
        $this->assertSame(['Diseñador', 'Community Manager'], $result['tareas'][1]['roles_sugeridos']);

        Carbon::setTestNow();
    }

    public function test_automatic_mode_never_calls_groq_and_builds_the_operational_campaign(): void
    {
        Carbon::setTestNow('2026-08-24 10:00:00');

        $company = new Empresa([
            'nombre_empresa' => 'Lumina',
            'descripcion' => 'Asesoria previsional',
            'resumen_ejecutivo' => 'La empresa busca captar nuevos clientes.',
        ]);
        $company->setRelation('respuestasCuestionario', collect());
        $contractedPlan = new Plan(['nombre' => 'Plan profesional']);
        $contractedPlan->setRelation('planCaracteristicas', collect());
        $subscription = new Suscripcion(['fecha_fin' => '2026-09-24']);
        $subscription->setRelation('empresa', $company);
        $subscription->setRelation('plan', $contractedPlan);
        $marketingPlan = new PlanMarketing([
            'contenido' => <<<'MARKDOWN'
## 10. Calendario Operativo Mensual
| Semana | Tema | Objetivo | CTA | Posts |
|---|---|---|---|---|
| 1 | Educacion | Informar | Escribenos | 1. **Lunes** - Post educativo. <br>2. **Miercoles** - Carrusel de beneficios. |
MARKDOWN,
        ]);

        Http::fake();

        $result = app(CampaignBlueprintService::class)->generate($subscription, $marketingPlan, 'automatico');

        Http::assertNothingSent();
        $this->assertSame('automatic_rules', $result['generation_source']);
        $this->assertNull($result['generation_warning']);
        $this->assertCount(4, $result['tareas']);
        $this->assertSame('Planificar la ejecucion de la campana', str($result['tareas'][0]['titulo'])->ascii()->toString());
        $this->assertSame('post', $result['tareas'][1]['tipo_contenido']);
        $this->assertSame('carrusel', $result['tareas'][2]['tipo_contenido']);
        $this->assertSame('Monitorear y reportar resultados', $result['tareas'][3]['titulo']);

        Carbon::setTestNow();
    }
}
