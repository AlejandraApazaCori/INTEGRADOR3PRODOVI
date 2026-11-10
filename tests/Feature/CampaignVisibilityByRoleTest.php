<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignVisibilityByRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_manager_sees_only_their_active_and_finished_campaigns(): void
    {
        [$admin, $manager, $otherManager, $designer] = $this->staff();
        $ownActive = $this->campaign('Campaña propia activa', $admin, $manager);
        $ownFinished = $this->campaign('Campaña propia finalizada', $admin, $manager, 'finalizada');
        $other = $this->campaign('Campaña de otra persona', $admin, $otherManager);

        $this->actingAs($manager)->get(route('administrador.campañas.index'))
            ->assertOk()
            ->assertSee($ownActive->nombre)
            ->assertSee($ownFinished->nombre)
            ->assertDontSee($other->nombre)
            ->assertSee('Clientes sin Campaña Activa')
            ->assertSee('Campañas Finalizadas');

        $this->get(route('administrador.campañas.show', $other))->assertNotFound();
        $this->get(route('administrador.campañas.edit', $other))->assertNotFound();
        $this->get(route('administrador.campañas.calendario', $other))->assertNotFound();
    }

    public function test_designer_sees_only_active_campaigns_where_they_are_on_the_team(): void
    {
        [$admin, $manager, $otherManager, $designer] = $this->staff();
        $assigned = $this->campaign('Diseño asignado activo', $admin, $manager);
        $assigned->disenadores()->attach($designer);
        $unassigned = $this->campaign('Diseño no asignado', $admin, $manager);
        $finished = $this->campaign('Diseño asignado finalizado', $admin, $manager, 'finalizada');
        $finished->disenadores()->attach($designer);

        $this->actingAs($designer)->get(route('administrador.campañas.index'))
            ->assertOk()
            ->assertSee($assigned->nombre)
            ->assertDontSee($unassigned->nombre)
            ->assertDontSee($finished->nombre)
            ->assertDontSee('Clientes sin Campaña Activa')
            ->assertDontSee('Campañas Finalizadas');

        $this->get(route('administrador.campañas.show', $unassigned))->assertNotFound();
        $this->get(route('administrador.campañas.show', $finished))->assertNotFound();
    }

    public function test_designer_without_campaign_assignment_sees_no_campaigns(): void
    {
        [$admin, $manager, $otherManager, $designer] = $this->staff();
        $campaign = $this->campaign('Campaña ajena sin asignación', $admin, $manager);

        $this->actingAs($designer)->get(route('administrador.campañas.index'))
            ->assertOk()
            ->assertDontSee($campaign->nombre)
            ->assertSee('No hay campañas activas en este momento');
        $this->get(route('administrador.campañas.show', $campaign))->assertNotFound();
    }

    public function test_administrator_keeps_access_to_all_campaigns(): void
    {
        [$admin, $manager, $otherManager] = $this->staff();
        $campaign = $this->campaign('Campaña de otro equipo', $admin, $otherManager);

        $this->actingAs($admin)->get(route('administrador.campañas.index'))
            ->assertOk()
            ->assertSee($campaign->nombre)
            ->assertSee('Clientes sin Campaña Activa')
            ->assertSee('Campañas Finalizadas');
        $this->get(route('administrador.campañas.show', $campaign))->assertOk();
    }

    private function staff(): array
    {
        $permission = Permission::create(['nombre_permiso' => 'Gestionar campañas', 'slug' => 'gestionar-campanas']);
        $roles = collect(['Administrador', 'Community Manager', 'Diseñador'])
            ->mapWithKeys(function (string $name) use ($permission) {
                $role = Role::create(['nombre_rol' => $name]);
                $role->permissions()->attach($permission);

                return [$name => $role];
            });
        $users = [User::factory()->create(), User::factory()->create(), User::factory()->create(), User::factory()->create()];
        foreach (['Administrador', 'Community Manager', 'Community Manager', 'Diseñador'] as $index => $role) {
            $users[$index]->roles()->attach($roles[$role]);
        }

        return $users;
    }

    private function campaign(string $name, User $admin, User $manager, string $state = 'activa'): Campania
    {
        $client = User::factory()->create();

        return Campania::create([
            'nombre' => $name,
            'descripcion' => 'Campaña para comprobar la visibilidad por responsable.',
            'fecha_inicio' => now()->subWeek()->toDateString(),
            'fecha_fin' => $state === 'finalizada' ? now()->subDay()->toDateString() : now()->addMonth()->toDateString(),
            'estado' => $state,
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
            'es_borrador' => false,
        ]);
    }
}
