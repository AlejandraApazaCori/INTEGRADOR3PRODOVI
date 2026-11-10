<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Pago;
use App\Models\User;
use App\Services\CampaignAudienceService;
use Carbon\Carbon;
use Database\Seeders\CampaniasDemoSeeder;
use Database\Seeders\ClientesCampaniasNuevasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientesCampaniasNuevasSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_new_batch_is_complete_idempotent_and_preserves_old_campaigns(): void
    {
        Carbon::setTestNow('2026-11-10 12:00:00');
        $this->seed(CampaniasDemoSeeder::class);
        $oldCampaigns = Campania::query()->where('nombre', 'like', 'DEMO %')->pluck('id', 'nombre');
        $oldCampaignRows = Campania::query()->where('nombre', 'like', 'DEMO %')->orderBy('id')->get()->toArray();

        $this->seed(ClientesCampaniasNuevasSeeder::class);
        $this->seed(ClientesCampaniasNuevasSeeder::class);

        $this->assertCount(12, $oldCampaigns);
        $this->assertSame($oldCampaigns->all(), Campania::query()->where('nombre', 'like', 'DEMO %')->pluck('id', 'nombre')->all());
        $this->assertSame($oldCampaignRows, Campania::query()->where('nombre', 'like', 'DEMO %')->orderBy('id')->get()->toArray());

        $clients = User::query()->whereIn('email', array_map(
            fn (int $number) => sprintf('cliente.campania.%02d@demo.prodovi.test', $number),
            range(101, 111)
        ))->get();
        $this->assertCount(11, $clients);

        $clientIds = $clients->pluck('id');
        $campaigns = Campania::query()->whereIn('usuario_cliente_id', $clientIds)
            ->with(['tareas.archivos', 'mensajes', 'reuniones'])->get();
        $this->assertCount(6, $campaigns);
        $this->assertTrue($campaigns->every(fn (Campania $campaign) =>
            $campaign->estado === 'activa'
            && ! str_starts_with($campaign->nombre, 'DEMO')
            && $campaign->tareas->count() === 4
            && $campaign->tareas->flatMap->archivos->isNotEmpty()
            && $campaign->mensajes->count() === 3
            && $campaign->reuniones->count() === 2
            && ! str_contains($campaign->publico_objetivo, 'tipo_edades')
            && count(app(CampaignAudienceService::class)->parse($campaign->publico_objetivo)) === 2
        ));

        $companies = Empresa::query()->whereIn('usuario_id', $clientIds)
            ->with(['respuestasCuestionario', 'planesMarketing', 'recursos', 'suscripcion'])->get();
        $this->assertCount(11, $companies);
        $this->assertTrue($companies->every(fn (Empresa $company) =>
            $company->suscripcion?->estado === 'activa'
            && $company->respuestasCuestionario->isNotEmpty()
            && $company->planesMarketing->isNotEmpty()
            && $company->recursos->count() === 3
        ));
        $this->assertSame(11, Pago::query()->whereIn('usuario_id', $clientIds)->where('estado', 'completado')->count());
        $this->assertSame(5, $clients->filter(fn (User $client) =>
            ! $campaigns->contains('usuario_cliente_id', $client->id)
        )->count());
        $this->assertDatabaseCount('campanias', 18);
        $this->assertSame(0, \DB::table('social_accounts')->whereIn('user_id', $clientIds)->count());
    }
}
