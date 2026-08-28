<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\SocialAccount;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientCompanyAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['app.timezone' => 'America/La_Paz', 'facebook.api_version' => 'v25.0']);
    }

    public function test_existing_client_analytics_are_preserved_and_company_insights_are_appended(): void
    {
        [$client, $companies] = $this->clientWithCompanies();

        $this->actingAs($client)
            ->get(route('clientes.analiticas'))
            ->assertOk()
            ->assertSee('id="metricsContainer"', false)
            ->assertSee('Analiticas de Rendimiento')
            ->assertSee('id="engagementChart"', false)
            ->assertSee('id="reachChart"', false)
            ->assertSee('id="conversionChart"', false)
            ->assertSee('id="client-analytics-company"', false)
            ->assertSee('data-company-dropdown', false)
            ->assertSee('class="client-company-trigger"', false)
            ->assertSee('data-period-dropdown', false)
            ->assertSee('class="meta-period-trigger"', false)
            ->assertSee($companies[0]->nombre_empresa)
            ->assertSee($companies[1]->nombre_empresa)
            ->assertSee(route('clientes.analiticas.empresa.datos', $companies[0]), false)
            ->assertSee(route('clientes.analiticas.empresa.datos', $companies[1]), false)
            ->assertSee('window.loadCampaignAnalytics?.();', false)
            ->assertDontSee('id="followersGrowthChart"', false)
            ->assertDontSee('id="engagementDistributionChart"', false)
            ->assertDontSee('id="ageGenderChart"', false);
    }

    public function test_each_company_uses_only_its_own_connected_social_accounts(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        [$firstCompany, $secondCompany] = $companies;
        $this->connect($client, $firstCompany, 'facebook_page', 'page-company-a');
        $this->connect($client, $secondCompany, 'instagram', 'ig-company-b');

        Http::fake(function (Request $request) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/page-company-a')) {
                return Http::response(['id' => 'page-company-a', 'name' => 'Facebook Empresa A', 'followers_count' => 55]);
            }
            if (str_ends_with($path, '/ig-company-b')) {
                return Http::response(['id' => 'ig-company-b', 'username' => 'empresa.b', 'followers_count' => 89]);
            }

            return Http::response(['data' => []]);
        });

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.empresa.datos', $firstCompany))
            ->assertOk()
            ->assertJsonPath('company.id', $firstCompany->id)
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.account.id', 'page-company-a')
            ->assertJsonPath('platforms.facebook.totals.followers', 55)
            ->assertJsonPath('platforms.instagram.connected', false);

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.empresa.datos', $secondCompany))
            ->assertOk()
            ->assertJsonPath('company.id', $secondCompany->id)
            ->assertJsonPath('platforms.facebook.connected', false)
            ->assertJsonPath('platforms.instagram.connected', true)
            ->assertJsonPath('platforms.instagram.account.id', 'ig-company-b')
            ->assertJsonPath('platforms.instagram.totals.followers', 89);
    }

    public function test_a_client_cannot_read_another_clients_company_insights(): void
    {
        [$client] = $this->clientWithCompanies();
        [$otherClient, $otherCompanies] = $this->clientWithCompanies('Otra');

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.empresa.datos', $otherCompanies[0]))
            ->assertNotFound();

        $this->actingAs($otherClient)
            ->getJson(route('clientes.analiticas.empresa.datos', $otherCompanies[0]))
            ->assertOk();
    }

    private function clientWithCompanies(string $suffix = ''): array
    {
        $client = User::factory()->create();
        $admin = User::factory()->create();
        $manager = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Plan '.$suffix.uniqid(), 'subtitulo' => 'Marketing', 'precio' => 100,
            'moneda' => 'BS', 'periodo_facturacion' => 'mes',
        ]);
        $companies = collect(['Empresa A '.$suffix, 'Empresa B '.$suffix])->map(function (string $name) use ($client, $plan) {
            $subscription = Suscripcion::create([
                'usuario_id' => $client->id, 'plan_id' => $plan->id, 'estado' => 'activa',
                'fecha_inicio' => now(), 'fecha_fin' => now()->addMonth(), 'vigencia_activada_at' => now(),
            ]);

            return Empresa::create([
                'usuario_id' => $client->id, 'suscripcion_id' => $subscription->id,
                'nombre_empresa' => trim($name), 'tipo_empresa' => 'Servicios',
            ]);
        })->values();

        Campania::create([
            'nombre' => 'Campaña activa '.$suffix, 'descripcion' => 'Mantiene las analíticas actuales',
            'fecha_inicio' => now()->subWeek()->toDateString(), 'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa', 'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id, 'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $companies[0]->suscripcion_id,
        ]);

        return [$client, $companies->all()];
    }

    private function connect(User $user, Empresa $company, string $provider, string $id): void
    {
        SocialAccount::create([
            'user_id' => $user->id, 'empresa_id' => $company->id, 'provider' => $provider,
            'provider_user_id' => $id, 'username' => $provider.'.'.$company->id,
            'display_name' => 'Cuenta '.$company->nombre_empresa, 'access_token' => 'valid-token',
        ]);
    }
}
