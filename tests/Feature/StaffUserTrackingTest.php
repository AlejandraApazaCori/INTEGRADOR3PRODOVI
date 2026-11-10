<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\User;
use Database\Seeders\CampaniasDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUserTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_list_and_campaign_drilldown_show_only_the_selected_persons_work(): void
    {
        $this->seed(CampaniasDemoSeeder::class);
        $admin = User::query()->whereHas('roles', fn ($q) => $q->where('nombre_rol', 'Administrador'))->firstOrFail();
        $manager = User::query()->whereHas('roles', fn ($q) => $q->where('nombre_rol', 'Community Manager'))->firstOrFail();
        $campaign = Campania::query()->where('community_manager_id', $manager->id)->firstOrFail();
        $client = $campaign->cliente;

        $this->actingAs($admin)
            ->get(route('administrador.usuarios.index', ['search' => $manager->email]))
            ->assertOk()
            ->assertSee(route('administrador.usuarios.seguimiento', $manager))
            ->assertDontSee(route('administrador.usuarios.historial-campanias', $manager));

        $this->get(route('administrador.usuarios.view', $manager))
            ->assertRedirect(route('administrador.usuarios.seguimiento', $manager));

        $this->get(route('administrador.usuarios.seguimiento', $manager))
            ->assertOk()
            ->assertSee($campaign->nombre)
            ->assertSee('Tareas asignadas')
            ->assertSee('Archivos subidos');

        $this->get(route('administrador.usuarios.seguimiento.campania', [$manager, $campaign]))
            ->assertOk()
            ->assertSee('Tareas y entregables')
            ->assertSee('Archivos subidos por');

        $this->get(route('administrador.usuarios.seguimiento', $client))->assertNotFound();
        $this->get(route('administrador.usuarios.seguimiento.campania', [$client, $campaign]))->assertNotFound();
    }
}
