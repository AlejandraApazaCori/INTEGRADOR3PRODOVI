<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\User;
use Database\Seeders\CampaniasDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCampaignHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_table_links_to_a_history_with_only_that_clients_campaigns(): void
    {
        $this->seed(CampaniasDemoSeeder::class);
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Administrador'))->firstOrFail();
        $active = Campania::query()->where('estado', 'activa')->firstOrFail();
        $client = $active->cliente;
        $otherCampaign = Campania::query()->where('usuario_cliente_id', '!=', $client->id)->firstOrFail();

        $finished = $active->replicate();
        $finished->nombre = 'Campaña anterior del mismo cliente';
        $finished->estado = 'finalizada';
        $finished->fecha_inicio = now()->subMonths(2)->toDateString();
        $finished->fecha_fin = now()->subMonth()->toDateString();
        $finished->save();

        $deleted = $active->replicate();
        $deleted->nombre = 'Campaña archivada del mismo cliente';
        $deleted->save();
        $deleted->delete();

        $this->actingAs($admin)
            ->get(route('administrador.usuarios.index', ['search' => $client->email]))
            ->assertOk()
            ->assertSee(route('administrador.usuarios.historial-campanias', $client))
            ->assertSee('Historial de campañas');

        $this->get(route('administrador.usuarios.historial-campanias', $client))
            ->assertOk()
            ->assertSee($active->nombre)
            ->assertSee($finished->nombre)
            ->assertSee($deleted->nombre)
            ->assertSee('Activa')
            ->assertSee('Finalizada')
            ->assertSee('Eliminada')
            ->assertSee($active->suscripcion->empresa->nombre_empresa)
            ->assertViewHas('campaigns', fn ($campaigns) =>
                $campaigns->contains('id', $active->id)
                && $campaigns->contains('id', $finished->id)
                && ! $campaigns->contains('id', $otherCampaign->id)
            );
    }

    public function test_history_has_a_clear_empty_state_and_requires_authentication(): void
    {
        $client = User::factory()->create();
        $admin = User::factory()->create();

        $this->get(route('administrador.usuarios.historial-campanias', $client))
            ->assertRedirect(route('login'));

        $this->actingAs($admin)
            ->get(route('administrador.usuarios.historial-campanias', $client))
            ->assertOk()
            ->assertSee('Este usuario todavía no tiene campañas');
    }
}
