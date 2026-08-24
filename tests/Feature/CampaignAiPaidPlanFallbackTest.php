<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\CampañasController;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampaignAiPaidPlanFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_campaign_from_the_paid_plan_when_the_strategic_document_does_not_exist(): void
    {
        $user = User::factory()->create();
        $planId = DB::table('plan')->insertGetId([
            'nombre' => 'Marketing Junior',
            'subtitulo' => 'Plan inicial',
            'precio' => 500,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $suscripcionId = DB::table('suscripciones')->insertGetId([
            'usuario_id' => $user->id,
            'plan_id' => $planId,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'vigencia_activada_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pagos')->insert([
            'usuario_id' => $user->id,
            'suscripcion_id' => $suscripcionId,
            'plan_id' => $planId,
            'monto' => 500,
            'moneda' => 'BS',
            'metodo' => 'qr',
            'estado' => 'completado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $empresaId = DB::table('empresas')->insertGetId([
            'usuario_id' => $user->id,
            'suscripcion_id' => $suscripcionId,
            'nombre_empresa' => 'Lumina consultora',
            'tipo_empresa' => 'Servicios',
            'descripcion' => 'Consultora especializada en estrategia empresarial.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new CampañasController())->obtenerPlanIA(
            Request::create('/', 'GET', ['suscripcion_id' => $suscripcionId]),
            Empresa::findOrFail($empresaId)
        );

        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Campaña Marketing Junior: Lumina consultora', $data['nombre']);
        $this->assertStringContainsString('Marketing Junior', $data['descripcion']);
    }
}
