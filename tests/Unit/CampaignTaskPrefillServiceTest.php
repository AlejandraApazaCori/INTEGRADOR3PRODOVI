<?php

namespace Tests\Unit;

use App\Models\PlanMarketing;
use App\Services\CampaignTaskPrefillService;
use Tests\TestCase;

class CampaignTaskPrefillServiceTest extends TestCase
{
    public function test_it_converts_the_monthly_operational_calendar_into_editable_team_tasks(): void
    {
        $plan = new PlanMarketing([
            'contenido' => <<<'MARKDOWN'
## 9. Estrategia general
Contenido previo.

## 10. Calendario Operativo Mensual
| Semana | Tema central | Objetivo | CTA | Posts |
|---|---|---|---|---|
| **1** | Educación | Informar | Escríbenos | 1. **Lunes** – Infografía sobre el servicio. <br>2. **Miércoles** – Carrusel de beneficios. |
| **2** | Conversión | Generar consultas | Agenda ahora | 1. **Viernes** – Reel de testimonio. |

## 11. Indicadores
Alcance e interacciones.
MARKDOWN,
        ]);

        $tasks = app(CampaignTaskPrefillService::class)->build($plan, '2026-08-24', '2026-09-24');

        $this->assertCount(3, $tasks);
        $this->assertSame('Post · Semana 1 · Lunes', $tasks[0]['titulo']);
        $this->assertSame('Diseñador', $tasks[0]['rol_sugerido']);
        $this->assertTrue($tasks[0]['requiere_aprobacion']);
        $this->assertSame(['Diseñador', 'Community Manager'], $tasks[0]['roles_sugeridos']);
        $this->assertSame('carrusel', $tasks[1]['tipo_contenido']);
        $this->assertSame('2026-09-04', $tasks[2]['fecha_limite']);
        $this->assertTrue($tasks[2]['visible_cliente']);
    }
}
