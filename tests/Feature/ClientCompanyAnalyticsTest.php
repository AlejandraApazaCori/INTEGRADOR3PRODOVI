<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\SocialAccount;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\MetaCampaignAnalyticsService;
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
            ->assertSee(route('clientes.analiticas.load-view', ['meta' => 1, 'empresa_id' => $companies[0]->id]))
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

    public function test_legacy_accounts_are_resolved_for_the_clients_first_company(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $firstCompany = collect($companies)->sortBy('id')->first();

        SocialAccount::create([
            'user_id' => $client->id, 'empresa_id' => null, 'provider' => 'facebook_page',
            'provider_user_id' => 'legacy-page', 'display_name' => 'Página heredada',
            'access_token' => 'legacy-page-token',
        ]);
        SocialAccount::create([
            'user_id' => $client->id, 'empresa_id' => null, 'provider' => 'instagram',
            'provider_user_id' => 'legacy-instagram', 'display_name' => 'Instagram heredado',
            'access_token' => 'legacy-instagram-token',
        ]);

        Http::fake(fn () => Http::response(['data' => []]));

        $this->actingAs($client)
            ->get(route('clientes.analiticas'))
            ->assertOk()
            ->assertSee('Facebook + Instagram');

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.empresa.datos', $firstCompany))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.instagram.connected', true);
    }

    public function test_existing_load_view_route_can_return_company_meta_analytics_as_fallback(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $this->connect($client, $companies[0], 'instagram', 'fallback-instagram');
        Http::fake(fn () => Http::response(['data' => []]));

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.load-view', [
                'meta' => 1,
                'empresa_id' => $companies[0]->id,
                'days' => 7,
            ]))
            ->assertOk()
            ->assertJsonPath('company.id', $companies[0]->id)
            ->assertJsonPath('period.days', 7)
            ->assertJsonPath('platforms.instagram.connected', true);
    }

    public function test_reach_report_uses_real_company_meta_analytics(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $company = $companies[1];
        $analytics = [
            'period' => ['days' => 30, 'since' => '2026-08-03', 'until' => '2026-09-01'],
            'generated_at' => now()->toIso8601String(),
            'platforms' => [
                'facebook' => [
                    'platform' => 'facebook', 'connected' => true,
                    'totals' => ['reach' => 1200],
                    'posts' => [[
                        'platform' => 'facebook', 'type' => 'PHOTO', 'reach' => 700,
                        'timestamp' => '2026-08-20T15:00:00-04:00', 'caption' => 'Publicación real',
                    ]],
                ],
                'instagram' => [
                    'platform' => 'instagram', 'connected' => true,
                    'totals' => ['reach' => 800],
                    'posts' => [[
                        'platform' => 'instagram', 'type' => 'REELS', 'reach' => 600,
                        'timestamp' => '2026-08-21T18:00:00-04:00', 'caption' => 'Reel real',
                    ]],
                ],
            ],
            'summary' => [
                'totals' => ['reach' => 2000],
                'audience' => ['cities' => [['name' => 'La Paz', 'value' => 450]], 'countries' => []],
            ],
        ];
        $service = \Mockery::mock(MetaCampaignAnalyticsService::class);
        $service->shouldReceive('forCompany')
            ->once()
            ->with(\Mockery::on(fn (Empresa $resolved) => $resolved->is($company)), 30)
            ->andReturn($analytics);
        $this->app->instance(MetaCampaignAnalyticsService::class, $service);

        $this->actingAs($client)
            ->get(route('clientes.analiticas.reporte-alcance', [
                'view' => '30dias',
                'empresa_id' => $company->id,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_followers_report_uses_real_company_meta_analytics(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $company = $companies[0];
        $analytics = [
            'period' => [
                'days' => 30, 'since' => '2026-08-03', 'until' => '2026-09-01',
                'insights_since' => '2026-08-03', 'insights_limited' => false,
            ],
            'generated_at' => now()->toIso8601String(),
            'platforms' => [
                'facebook' => [
                    'platform' => 'facebook', 'connected' => true,
                    'totals' => ['followers' => 125],
                    'followers' => ['values' => [110, 115, 125]],
                ],
                'instagram' => [
                    'platform' => 'instagram', 'connected' => true,
                    'totals' => ['followers' => 310],
                    'followers' => ['values' => [300, 305, 310]],
                ],
            ],
            'summary' => ['totals' => ['followers' => 435]],
        ];
        $service = \Mockery::mock(MetaCampaignAnalyticsService::class);
        $service->shouldReceive('forCompany')
            ->once()
            ->with(\Mockery::on(fn (Empresa $resolved) => $resolved->is($company)), 30)
            ->andReturn($analytics);
        $this->app->instance(MetaCampaignAnalyticsService::class, $service);

        $this->actingAs($client)
            ->get(route('clientes.analiticas.reporte-seguidores', [
                'view' => '30dias',
                'empresa_id' => $company->id,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_ctr_report_uses_real_company_meta_analytics(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $company = $companies[1];
        $analytics = [
            'period' => ['days' => 30, 'since' => '2026-08-03', 'until' => '2026-09-01'],
            'generated_at' => now()->toIso8601String(),
            'platforms' => [
                'facebook' => [
                    'platform' => 'facebook', 'connected' => true,
                    'totals' => ['views' => 10000, 'clicks' => 250, 'reach' => 8000, 'engagement' => 500],
                    'posts' => [],
                ],
                'instagram' => [
                    'platform' => 'instagram', 'connected' => true,
                    'totals' => ['views' => null, 'clicks' => null, 'reach' => 5000, 'engagement' => 300],
                    'posts' => [],
                ],
            ],
            'summary' => ['totals' => ['views' => 10000, 'clicks' => 250]],
        ];
        $service = \Mockery::mock(MetaCampaignAnalyticsService::class);
        $service->shouldReceive('forCompany')
            ->once()
            ->with(\Mockery::on(fn (Empresa $resolved) => $resolved->is($company)), 30)
            ->andReturn($analytics);
        $this->app->instance(MetaCampaignAnalyticsService::class, $service);

        $this->actingAs($client)
            ->get(route('clientes.analiticas.reporte-ctr', [
                'view' => '30dias',
                'empresa_id' => $company->id,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_legacy_facebook_profile_with_page_credentials_can_supply_page_insights(): void
    {
        [$client, $companies] = $this->clientWithCompanies();
        $firstCompany = collect($companies)->sortBy('id')->first();
        SocialAccount::create([
            'user_id' => $client->id,
            'empresa_id' => null,
            'provider' => 'facebook',
            'provider_user_id' => 'facebook-user',
            'display_name' => 'Perfil autorizado',
            'access_token' => 'user-token',
            'metadata' => [
                'pages' => [[
                    'id' => 'page-from-profile',
                    'name' => 'Página desde perfil',
                    'access_token' => 'page-token',
                ]],
            ],
        ]);

        Http::fake(function (Request $request) {
            if (str_ends_with((string) parse_url($request->url(), PHP_URL_PATH), '/page-from-profile')) {
                return Http::response([
                    'id' => 'page-from-profile',
                    'name' => 'Página desde perfil',
                    'followers_count' => 44,
                ]);
            }

            return Http::response(['data' => []]);
        });

        $this->actingAs($client)
            ->getJson(route('clientes.analiticas.empresa.datos', $firstCompany))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.account.id', 'page-from-profile')
            ->assertJsonPath('platforms.facebook.totals.followers', 44);
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
