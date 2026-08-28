<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\RecursoEmpresa;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_can_see_client_resources_and_share_its_own_resources(): void
    {
        Storage::fake('public');
        [$admin, $client, $company, $campaign] = $this->campaignContext();

        $clientResource = RecursoEmpresa::create([
            'empresa_id' => $company->id,
            'tipo' => 'enlace',
            'nombre' => 'Material del cliente',
            'url' => 'https://drive.google.com/example-client',
            'origen' => 'cliente',
            'visible_cliente' => true,
            'creado_por_id' => $client->id,
        ]);

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#recursos')
            ->assertOk()
            ->assertSee('data-campaign-tab="resources"', false)
            ->assertSee('Recursos del cliente')
            ->assertSee($clientResource->nombre);

        $upload = $this->actingAs($admin)->post(
            route('administrador.campañas.recursos.store', $campaign),
            ['imagenes' => [UploadedFile::fake()->image('equipo.png')]]
        );

        $upload->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos');
        $adminResource = RecursoEmpresa::where('nombre', 'equipo.png')->firstOrFail();
        $this->assertSame('administracion', $adminResource->origen);
        $this->assertFalse($adminResource->visible_cliente);
        Storage::disk('public')->assertExists($adminResource->archivo_path);

        $this->actingAs($client)
            ->get(route('clientes.recursos', ['empresa_id' => $company->id]))
            ->assertOk()
            ->assertSee('Material del cliente')
            ->assertDontSee('equipo.png');

        $this->actingAs($admin)->patch(
            route('administrador.campañas.recursos.visibilidad', [$campaign, $adminResource]),
            ['visible_cliente' => 1]
        )->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos');

        $this->actingAs($client)
            ->get(route('clientes.recursos', ['empresa_id' => $company->id]))
            ->assertOk()
            ->assertSee('equipo.png')
            ->assertSee('Compartido por el equipo');

        $this->actingAs($client)
            ->delete(route('clientes.recursos.destroy', $adminResource))
            ->assertForbidden();

        $this->actingAs($admin)->patch(
            route('administrador.campañas.recursos.nombre', [$campaign, $clientResource]),
            ['nombre' => 'Nombre actualizado del cliente']
        )->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos');
        $this->assertSame('Nombre actualizado del cliente', $clientResource->fresh()->nombre);

        $this->actingAs($admin)->patch(
            route('administrador.campañas.recursos.nombre', [$campaign, $adminResource]),
            ['nombre' => 'Nombre actualizado del equipo']
        )->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos');
        $this->assertSame('Nombre actualizado del equipo', $adminResource->fresh()->nombre);

        $this->actingAs($admin)
            ->delete(route('administrador.campañas.recursos.destroy', [$campaign, $clientResource]))
            ->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos');
        $this->assertSoftDeleted('recursos_empresa', ['id' => $clientResource->id]);
    }

    public function test_campaign_resources_show_a_message_when_client_uploaded_nothing(): void
    {
        [$admin, , , $campaign] = $this->campaignContext();

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#recursos')
            ->assertOk()
            ->assertSee('El cliente no subió ningún recurso');

        $this->actingAs($admin)
            ->post(route('administrador.campañas.recursos.store', $campaign))
            ->assertRedirect(route('administrador.campañas.show', $campaign).'#recursos')
            ->assertSessionHasErrors('recursos');
    }

    private function campaignContext(): array
    {
        $admin = User::factory()->create();
        $client = User::factory()->create();
        $manager = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Plan mensual',
            'subtitulo' => 'Marketing digital',
            'precio' => 100,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
        ]);
        $subscription = Suscripcion::create([
            'usuario_id' => $client->id,
            'plan_id' => $plan->id,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'vigencia_activada_at' => now(),
        ]);
        $company = Empresa::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $subscription->id,
            'nombre_empresa' => 'Empresa de recursos',
            'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña con recursos',
            'descripcion' => 'Prueba de recursos',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);

        return [$admin, $client, $company, $campaign];
    }
}
