<?php

namespace Tests\Feature;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentFilterSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_cards_use_the_same_filters_as_the_table(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['nombre_rol' => 'Administrador']);
        $admin->roles()->attach($adminRole);

        $client = User::factory()->create(['name' => 'Cliente filtrado']);
        $basicPlan = $this->createPlan('Plan Básico', 100);
        $premiumPlan = $this->createPlan('Plan Premium', 250);
        $basicSubscription = $this->createSubscription($client, $basicPlan, 'activa');
        $premiumSubscription = $this->createSubscription($client, $premiumPlan, 'pendiente');

        Pago::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $basicSubscription->id,
            'plan_id' => $basicPlan->id,
            'codigo_pago' => 'PAGO-COMPLETADO-001',
            'monto' => 100,
            'moneda' => 'BS',
            'metodo' => 'qr',
            'estado' => 'completado',
            'fecha_pago' => now(),
        ]);
        Pago::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $premiumSubscription->id,
            'plan_id' => $premiumPlan->id,
            'codigo_pago' => 'PAGO-PENDIENTE-002',
            'monto' => 250,
            'moneda' => 'BS',
            'metodo' => 'fisico',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($admin)->get(route('administrador.pagos.index', [
            'payment_status' => 'pendiente',
            'method' => 'fisico',
            'plan' => $premiumPlan->id,
        ]));

        $response
            ->assertOk()
            ->assertViewHas('paymentSummary', fn (array $summary) => $summary === [
                'total_income' => '0,00 BS',
                'most_hired_plan' => 'Plan Premium',
                'total_records' => 1,
            ])
            ->assertSee('PAGO-PENDIENTE-002')
            ->assertDontSee('PAGO-COMPLETADO-001');
    }

    private function createPlan(string $name, float $price): Plan
    {
        return Plan::create([
            'nombre' => $name,
            'subtitulo' => $name,
            'precio' => $price,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
            'activo' => true,
        ]);
    }

    private function createSubscription(User $client, Plan $plan, string $status): Suscripcion
    {
        return Suscripcion::create([
            'usuario_id' => $client->id,
            'plan_id' => $plan->id,
            'estado' => $status,
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->addMonth(),
            'metodo_pago' => 'qr',
        ]);
    }
}
