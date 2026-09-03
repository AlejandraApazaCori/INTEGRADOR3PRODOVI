<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CampaniasDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaniasDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_creates_complete_campaign_data_without_social_connections_or_duplicates(): void
    {
        Carbon::setTestNow('2026-09-03 12:00:00');

        $this->seed(CampaniasDemoSeeder::class);
        $this->seed(CampaniasDemoSeeder::class);

        $campaigns = Campania::query()
            ->where('nombre', 'like', 'DEMO %')
            ->with(['suscripcion.empresa.planesMarketing', 'tareas.archivos', 'reuniones', 'mensajes'])
            ->get();

        $this->assertCount(12, $campaigns);
        $this->assertCount(2, $campaigns->where('estado', 'activa'));
        $this->assertCount(10, $campaigns->where('estado', 'finalizada'));
        $this->assertTrue($campaigns->where('estado', 'activa')->every(
            fn (Campania $campaign) => Carbon::parse($campaign->fecha_inicio)->isSameMonth(now())
                && Carbon::parse($campaign->fecha_fin)->greaterThan(now())
        ));
        $this->assertTrue($campaigns->where('estado', 'finalizada')->every(
            fn (Campania $campaign) => Carbon::parse($campaign->fecha_inicio)->year === now()->year
                && Carbon::parse($campaign->fecha_fin)->year === now()->year
                && Carbon::parse($campaign->fecha_fin)->lessThanOrEqualTo(now())
        ));
        $this->assertTrue($campaigns->every(
            fn (Campania $campaign) => $campaign->suscripcion?->empresa !== null
                && $campaign->suscripcion->empresa->planesMarketing->isNotEmpty()
                && $campaign->tareas->count() === 4
                && $campaign->tareas->flatMap->archivos->isNotEmpty()
                && $campaign->reuniones->count() === 2
                && $campaign->mensajes->count() === 3
        ));

        $clientIds = User::query()
            ->where('email', 'like', 'cliente.campania.%@demo.prodovi.test')
            ->pluck('id');
        $this->assertCount(12, $clientIds);
        $this->assertSame(12, Pago::query()->whereIn('usuario_id', $clientIds)->where('estado', 'completado')->count());
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $clientIds->first()]);

        $clientsWithoutActiveCampaign = Pago::query()
            ->whereIn('usuario_id', $clientIds)
            ->where('estado', 'completado')
            ->whereHas('suscripcion', fn ($query) => $query->where('estado', 'activa')->where('fecha_fin', '>', now()))
            ->whereDoesntHave('suscripcion.campanias', fn ($query) => $query
                ->where('fecha_fin', '>', now())
                ->whereIn('estado', ['activa', 'pausada']))
            ->count();

        $this->assertSame(0, $clientsWithoutActiveCampaign);

        $admin = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('nombre_rol', ['Super Administrador', 'Administrador']))
            ->firstOrFail();
        $activeCampaign = $campaigns->firstWhere('estado', 'activa');

        $this->actingAs($admin)
            ->get(route('administrador.campañas.index'))
            ->assertOk()
            ->assertSee($activeCampaign->nombre);

        $this->get(route('administrador.campañas.show', $activeCampaign))
            ->assertOk()
            ->assertSee($activeCampaign->suscripcion->empresa->nombre_empresa)
            ->assertSee('Centro de mensajes')
            ->assertSee('No conectado')
            ->assertSee('Conectar con Facebook')
            ->assertSee(route('clientes.social.redirect', [
                'provider' => 'facebook',
                'empresa_id' => $activeCampaign->suscripcion->empresa->id,
                'return_to' => 'admin_campaign',
                'campania_id' => $activeCampaign->id,
            ]));
    }
}
