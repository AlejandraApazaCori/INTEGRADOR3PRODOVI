<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\PlanMarketingController;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Services\ExecutiveSummaryFormatter;
use App\Services\MarketingPlanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use ReflectionMethod;
use Tests\TestCase;
use ZipArchive;

class MarketingPlanPdfTest extends TestCase
{
    public function test_it_generates_the_marketing_plan_pdf_with_formatted_sections(): void
    {
        $empresa = new Empresa(['nombre_empresa' => 'Empresa de prueba']);
        $suscripcion = new Suscripcion;
        $suscripcion->setRelation('plan', new Plan(['nombre' => 'Plan profesional']));
        $marketingPlan = new PlanMarketing([
            'contenido' => "## 1 Calendario\n1. **Lunes** – Infografía. 2. **Miércoles** – Carrusel.",
            'estado' => 'activo',
        ]);
        $marketingPlan->created_at = Carbon::parse('2026-08-24 12:00:00');
        $marketingPlan->setRelation('empresa', $empresa);
        $marketingPlan->setRelation('suscripcion', $suscripcion);
        $sections = (new ExecutiveSummaryFormatter)->sections($marketingPlan->contenido);

        $output = Pdf::loadView('administrador.planes-marketing.pdf', [
            'planMarketing' => $marketingPlan,
            'seccionesParseadas' => $sections,
        ])->output();

        $this->assertStringStartsWith('%PDF', $output);

        $method = new ReflectionMethod(PlanMarketingController::class, 'marketingPlanDocx');
        $document = $method->invoke(new PlanMarketingController(new MarketingPlanService, new ExecutiveSummaryFormatter), $marketingPlan);
        $path = tempnam(sys_get_temp_dir(), 'marketing-plan-doc-test-');
        file_put_contents($path, $document);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $this->assertStringContainsString('Plan de marketing empresarial', (string) $zip->getFromName('word/header1.xml'));
        $this->assertStringContainsString('Empresa de prueba', (string) $zip->getFromName('word/document.xml'));
        $this->assertFalse($zip->locateName('word/footer1.xml'));
        $zip->close();
        unlink($path);
    }
}
