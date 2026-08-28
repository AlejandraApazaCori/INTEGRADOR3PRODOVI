<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\PlanMarketing;
use App\Models\Role;
use App\Models\Suscripcion;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignEditWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_edited_with_the_advanced_creation_experience(): void
    {
        $admin = User::factory()->create();
        $client = User::factory()->create();
        $manager = User::factory()->create();
        $designer = User::factory()->create();
        $manager->roles()->attach(Role::create(['nombre_rol' => 'Community Manager']));
        $designer->roles()->attach(Role::create(['nombre_rol' => 'Diseñador']));

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
            'nombre_empresa' => 'Empresa de prueba',
            'tipo_empresa' => 'Servicios',
            'cuestionario_completado' => true,
        ]);
        PlanMarketing::create([
            'empresa_id' => $company->id,
            'suscripcion_id' => $subscription->id,
            'contenido' => 'Plan de marketing de prueba',
            'estado' => 'activo',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña original',
            'descripcion' => 'Descripción original',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);
        $campaign->disenadores()->attach($designer);
        $task = Tarea::create([
            'titulo' => 'Tarea original',
            'descripcion' => 'Contenido original',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'prioridad' => 'media',
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);
        $task->responsables()->attach($manager);

        $this->actingAs($admin)
            ->get(route('administrador.campañas.edit', $campaign))
            ->assertOk()
            ->assertSee('Editar campaña')
            ->assertSee('Campaña original')
            ->assertSee('Tarea original')
            ->assertSee('Tipo de público + edades')
            ->assertSee('Agregar público')
            ->assertSee('¿Estás seguro de cambiar de modo?')
            ->assertSee('se borrará del formulario todo lo anterior previamente guardado')
            ->assertSee('Sí, cambiar de modo')
            ->assertSee('onclick="requestCampaignModeChange(this, event)"', false)
            ->assertSee('window.requestCampaignModeChange', false)
            ->assertDontSee('data-mode="automatico" disabled', false)
            ->assertDontSee('data-mode="asistido" disabled', false)
            ->assertSee('TikTok')
            ->assertSee('WhatsApp')
            ->assertSee('Guardar cambios');

        $response = $this->actingAs($admin)->put(route('administrador.campañas.update', $campaign), [
            'nombre' => 'Campaña actualizada',
            'descripcion' => 'Descripción actualizada',
            'modo_creacion' => 'manual',
            'publicos_objetivo' => [[
                'tipo_edades' => 'Adultos planificadores (35-55 años)',
                'descripcion' => 'Buscan comprender sus aportes y asegurar el futuro de su familia.',
            ]],
            'community_manager_id' => $manager->id,
            'disenadores_ids' => [$designer->id],
            'estado' => 'pausada',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'canales' => ['Facebook', 'Instagram', 'TikTok', 'WhatsApp'],
            'indicadores' => ['Alcance'],
            'tareas' => [[
                'id' => $task->id,
                'titulo' => 'Tarea actualizada',
                'descripcion' => 'Contenido actualizado',
                'tipo_contenido' => 'post',
                'fecha_inicio' => now()->toDateString(),
                'fecha_limite' => now()->addWeek()->toDateString(),
                'prioridad' => 'alta',
                'rol_sugerido' => 'Community Manager',
                'requiere_aprobacion' => 1,
                'visible_cliente' => 1,
                'responsables_ids' => [$manager->id],
            ]],
        ]);

        $response->assertRedirect(route('administrador.campañas.show', $campaign));
        $this->assertDatabaseHas('campanias', ['id' => $campaign->id, 'nombre' => 'Campaña actualizada', 'estado' => 'pausada']);
        $this->assertDatabaseHas('tareas', ['id' => $task->id, 'titulo' => 'Tarea actualizada', 'prioridad' => 'alta']);
        $this->assertSame(['Facebook', 'Instagram', 'TikTok', 'WhatsApp'], $campaign->fresh()->canales);
        $this->assertSame(
            'Adultos planificadores (35-55 años): Buscan comprender sus aportes y asegurar el futuro de su familia.',
            $campaign->fresh()->publico_objetivo
        );
    }
}
