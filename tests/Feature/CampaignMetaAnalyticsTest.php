<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\SocialAccount;
use App\Models\Suscripcion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampaignMetaAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['app.timezone' => 'America/La_Paz', 'facebook.api_version' => 'v25.0']);
    }

    public function test_campaign_exposes_a_lazy_analytics_tab_without_mock_data(): void
    {
        [$admin, $campaign] = $this->campaignContext();
        Http::fake(fn () => Http::response([], 500));

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#analiticas')
            ->assertOk()
            ->assertSee('data-campaign-tab="analytics"', false)
            ->assertSee('data-campaign-panel="analytics"', false)
            ->assertSee('id="campaign-meta-analytics"', false)
            ->assertSee(route('administrador.campañas.analiticas.datos', $campaign), false)
            ->assertSee("'#analiticas': 'analytics'", false)
            ->assertDontSee('analiticas_por_campania.json');

        Http::assertNothingSent();
    }

    public function test_analytics_without_connected_accounts_returns_an_empty_safe_response(): void
    {
        [$admin, $campaign] = $this->campaignContext();
        Http::fake(fn () => Http::response([], 500));

        $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', [$campaign, 'days' => 30]))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', false)
            ->assertJsonPath('platforms.instagram.connected', false)
            ->assertJsonPath('summary.totals.posts', 0)
            ->assertJsonPath('period.timezone', 'America/La_Paz')
            ->assertJsonCount(0, 'errors');

        Http::assertNothingSent();
    }

    public function test_facebook_and_instagram_are_normalized_and_compared_using_real_api_payloads(): void
    {
        [$admin, $campaign, $company, $client] = $this->campaignContext();
        $this->connect($client, $company, 'facebook_page', 'page-10', 'facebook-token');
        $this->connect($client, $company, 'instagram', 'ig-20', 'instagram-token');

        $firstPublication = Carbon::parse('2026-08-17 10:15:00', 'America/La_Paz')->utc()->toIso8601String();
        $secondPublication = Carbon::parse('2026-08-24 10:45:00', 'America/La_Paz')->utc()->toIso8601String();

        Http::fake(function (Request $request) use ($firstPublication, $secondPublication) {
            $url = $request->url();
            $path = (string) parse_url($url, PHP_URL_PATH);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $data = array_merge($query, $request->data());

            if (str_ends_with($path, '/page-10')) {
                return Http::response(['id' => 'page-10', 'name' => 'Página real', 'followers_count' => 125], 200);
            }
            if (str_ends_with($path, '/page-10/published_posts')) {
                return Http::response(['data' => [
                    $this->facebookPost('fb-1', $firstPublication, 12, 3, 2),
                    $this->facebookPost('fb-2', $secondPublication, 18, 4, 1),
                ]], 200);
            }
            if (str_ends_with($path, '/page-10/insights')) {
                return $this->facebookInsightResponse((string) ($data['metric'] ?? ''));
            }
            if (str_contains($path, '/fb-') && str_ends_with($path, '/insights')) {
                return Http::response(['data' => [
                    ['name' => 'post_media_view', 'values' => [['value' => 700]]],
                    ['name' => 'post_media_viewers', 'values' => [['value' => 420]]],
                    ['name' => 'post_clicks', 'values' => [['value' => 17]]],
                ]], 200);
            }
            if (str_ends_with($path, '/ig-20')) {
                return Http::response([
                    'id' => 'ig-20', 'username' => 'cuenta.real', 'name' => 'Cuenta real',
                    'followers_count' => 310, 'media_count' => 2,
                ], 200);
            }
            if (str_ends_with($path, '/ig-20/media')) {
                return Http::response(['data' => [
                    $this->instagramMedia('ig-media-1', $firstPublication, 'REELS', 30, 5),
                    $this->instagramMedia('ig-media-2', $secondPublication, 'IMAGE', 21, 4),
                ]], 200);
            }
            if (str_ends_with($path, '/ig-20/insights')) {
                if (($data['metric'] ?? null) === 'follower_demographics') {
                    return $this->instagramDemographicResponse((string) ($data['breakdown'] ?? ''));
                }

                return $this->instagramAccountInsightResponse((string) ($data['metric'] ?? ''));
            }
            if (str_contains($path, '/ig-media-') && str_ends_with($path, '/insights')) {
                return $this->instagramMediaInsightResponse((string) ($data['metric'] ?? ''));
            }

            return Http::response(['error' => ['message' => 'Solicitud no contemplada en la prueba']], 400);
        });

        $response = $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', [$campaign, 'days' => 30]))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.account.id', 'page-10')
            ->assertJsonPath('platforms.facebook.totals.followers', 125)
            ->assertJsonPath('platforms.facebook.totals.posts', 2)
            ->assertJsonPath('platforms.facebook.top_posts.0.reach', 420)
            ->assertJsonPath('platforms.facebook.top_posts.0.clicks', 17)
            ->assertJsonPath('platforms.instagram.connected', true)
            ->assertJsonPath('platforms.instagram.account.id', 'ig-20')
            ->assertJsonPath('platforms.instagram.totals.followers', 310)
            ->assertJsonPath('platforms.instagram.totals.posts', 2)
            ->assertJsonPath('platforms.instagram.engagement.saves', 8)
            ->assertJsonPath('summary.totals.followers', 435)
            ->assertJsonPath('summary.totals.posts', 4)
            ->assertJsonPath('summary.best_posting_times.sufficient_data', true)
            ->assertJsonPath('summary.best_posting_times.best.0.day_name', 'Lunes')
            ->assertJsonPath('summary.best_posting_times.best.0.hour', 10)
            ->assertJsonPath('period.timezone', 'America/La_Paz');

        $this->assertNotEmpty($response->json('summary.followers.facebook'));
        $this->assertNotEmpty($response->json('summary.followers.instagram'));
        $this->assertNotEmpty($response->json('platforms.instagram.audience.cities'));
        $this->assertNotEmpty($response->json('platforms.instagram.audience.countries'));
    }

    public function test_facebook_only_keeps_its_charts_available_and_instagram_disconnected(): void
    {
        [$admin, $campaign, $company, $client] = $this->campaignContext();
        $this->connect($client, $company, 'facebook_page', 'page-only', 'facebook-token');
        $publishedAt = now('America/La_Paz')->subDay()->utc()->toIso8601String();

        Http::fake(function (Request $request) use ($publishedAt) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/page-only')) {
                return Http::response(['id' => 'page-only', 'name' => 'Solo Facebook', 'followers_count' => 80]);
            }
            if (str_ends_with($path, '/page-only/published_posts')) {
                return Http::response(['data' => [$this->facebookPost('fb-only-1', $publishedAt, 9, 2, 1)]]);
            }

            return Http::response(['data' => []]);
        });

        $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', $campaign))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.totals.followers', 80)
            ->assertJsonPath('platforms.facebook.totals.posts', 1)
            ->assertJsonPath('platforms.instagram.connected', false)
            ->assertJsonPath('summary.totals.followers', 80)
            ->assertJsonPath('summary.totals.posts', 1);
    }

    public function test_instagram_only_keeps_its_charts_available_and_facebook_disconnected(): void
    {
        [$admin, $campaign, $company, $client] = $this->campaignContext();
        $this->connect($client, $company, 'instagram', 'ig-only', 'instagram-token');
        $publishedAt = now('America/La_Paz')->subDay()->utc()->toIso8601String();

        Http::fake(function (Request $request) use ($publishedAt) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/ig-only')) {
                return Http::response(['id' => 'ig-only', 'username' => 'solo.instagram', 'followers_count' => 95]);
            }
            if (str_ends_with($path, '/ig-only/media')) {
                return Http::response(['data' => [$this->instagramMedia('ig-only-media', $publishedAt, 'IMAGE', 14, 3)]]);
            }
            if (str_ends_with($path, '/ig-only-media/insights')) {
                return Http::response(['data' => []]);
            }

            return Http::response(['data' => []]);
        });

        $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', $campaign))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', false)
            ->assertJsonPath('platforms.instagram.connected', true)
            ->assertJsonPath('platforms.instagram.totals.followers', 95)
            ->assertJsonPath('platforms.instagram.totals.posts', 1)
            ->assertJsonPath('summary.totals.followers', 95)
            ->assertJsonPath('summary.totals.posts', 1);
    }

    public function test_facebook_posts_fall_back_to_page_owned_content_when_user_content_permission_is_missing(): void
    {
        [$admin, $campaign, $company, $client] = $this->campaignContext();
        $this->connect($client, $company, 'facebook_page', 'page-limited', 'facebook-token');
        $publishedAt = now('America/La_Paz')->subDay()->utc()->toIso8601String();

        Http::fake(function (Request $request) use ($publishedAt) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $data = array_merge($query, $request->data());

            if (str_ends_with($path, '/page-limited')) {
                return Http::response(['id' => 'page-limited', 'name' => 'Página limitada', 'followers_count' => 70]);
            }
            if (str_ends_with($path, '/page-limited/published_posts')) {
                if (str_contains((string) ($data['fields'] ?? ''), 'comments')) {
                    return Http::response(['error' => ['message' => "(#10) Requires pages_read_user_content"]], 400);
                }

                return Http::response(['data' => [$this->facebookPost('fb-limited', $publishedAt, 0, 0, 0)]]);
            }
            if (str_ends_with($path, '/fb-limited/insights')) {
                return Http::response(['data' => []]);
            }

            return Http::response(['data' => []]);
        });

        $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', $campaign))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.totals.followers', 70)
            ->assertJsonPath('platforms.facebook.totals.posts', 1)
            ->assertJsonCount(0, 'errors');
    }

    public function test_meta_errors_do_not_break_the_campaign_analytics_endpoint(): void
    {
        [$admin, $campaign, $company, $client] = $this->campaignContext();
        $this->connect($client, $company, 'facebook_page', 'page-error', 'expired-token');
        Http::fake(fn () => Http::response([
            'error' => ['message' => 'Error validating access token', 'code' => 190],
        ], 400));

        $this->actingAs($admin)
            ->getJson(route('administrador.campañas.analiticas.datos', [$campaign, 'days' => 90]))
            ->assertOk()
            ->assertJsonPath('platforms.facebook.connected', true)
            ->assertJsonPath('platforms.facebook.totals.followers', null)
            ->assertJsonPath('platforms.facebook.totals.posts', 0)
            ->assertJsonPath('platforms.instagram.connected', false)
            ->assertJsonPath('errors.0.platform', 'facebook');
    }

    private function campaignContext(): array
    {
        $admin = User::factory()->create();
        $client = User::factory()->create();
        $manager = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Plan Meta', 'subtitulo' => 'Analíticas', 'precio' => 100,
            'moneda' => 'BS', 'periodo_facturacion' => 'mes',
        ]);
        $subscription = Suscripcion::create([
            'usuario_id' => $client->id, 'plan_id' => $plan->id, 'estado' => 'activa',
            'fecha_inicio' => now(), 'fecha_fin' => now()->addMonth(), 'vigencia_activada_at' => now(),
        ]);
        $company = Empresa::create([
            'usuario_id' => $client->id, 'suscripcion_id' => $subscription->id,
            'nombre_empresa' => 'Empresa Meta', 'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña con analíticas', 'descripcion' => 'Datos reales de Meta',
            'fecha_inicio' => now()->subMonth()->toDateString(), 'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa', 'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id, 'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);

        return [$admin, $campaign, $company, $client];
    }

    private function connect(User $user, Empresa $company, string $provider, string $id, string $token): SocialAccount
    {
        return SocialAccount::create([
            'user_id' => $user->id, 'empresa_id' => $company->id, 'provider' => $provider,
            'provider_user_id' => $id, 'username' => $provider.'.real',
            'display_name' => 'Cuenta '.$provider, 'access_token' => $token,
        ]);
    }

    private function facebookPost(string $id, string $timestamp, int $reactions, int $comments, int $shares): array
    {
        return [
            'id' => $id, 'message' => 'Contenido real', 'created_time' => $timestamp,
            'permalink_url' => 'https://facebook.com/'.$id,
            'reactions' => ['summary' => ['total_count' => $reactions]],
            'comments' => ['summary' => ['total_count' => $comments]],
            'shares' => ['count' => $shares],
        ];
    }

    private function instagramMedia(string $id, string $timestamp, string $type, int $likes, int $comments): array
    {
        return [
            'id' => $id, 'caption' => 'Contenido real de Instagram', 'timestamp' => $timestamp,
            'media_type' => $type === 'REELS' ? 'VIDEO' : $type, 'media_product_type' => $type,
            'permalink' => 'https://instagram.com/p/'.$id, 'like_count' => $likes, 'comments_count' => $comments,
        ];
    }

    private function facebookInsightResponse(string $metric)
    {
        $values = match ($metric) {
            'page_daily_follows' => [1, 2],
            'page_daily_unfollows' => [0, 1],
            'page_media_viewers' => [500, 650],
            'page_media_views' => [900, 1100],
            'page_total_actions' => [15, 19],
            default => null,
        };
        if ($metric === 'page_fans_gender_age') {
            $values = ['F.25-34' => 60, 'M.25-34' => 40];
        } elseif ($metric === 'page_fans_city') {
            $values = ['La Paz, Bolivia' => 70, 'Cochabamba, Bolivia' => 30];
        } elseif ($metric === 'page_fans_country') {
            $values = ['BO' => 90, 'PE' => 10];
        }
        if ($values === null) {
            return Http::response(['data' => []], 200);
        }
        $items = is_array($values) && array_is_list($values)
            ? collect($values)->map(fn ($value, $index) => [
                'value' => $value,
                'end_time' => Carbon::parse('2026-08-26 00:00:00', 'UTC')->addDays($index)->toIso8601String(),
            ])->all()
            : [['value' => $values]];

        return Http::response(['data' => [['name' => $metric, 'period' => 'day', 'values' => $items]]], 200);
    }

    private function instagramAccountInsightResponse(string $metric)
    {
        $values = match ($metric) {
            'follower_count' => [2, 3], 'reach' => [400, 520], 'views' => [800, 930],
            'profile_links_taps' => [10, 14], default => null,
        };
        if ($values === null) {
            return Http::response(['data' => []], 200);
        }

        return Http::response(['data' => [[
            'name' => $metric, 'period' => 'day',
            'values' => collect($values)->map(fn ($value, $index) => [
                'value' => $value,
                'end_time' => Carbon::parse('2026-08-26 00:00:00', 'UTC')->addDays($index)->toIso8601String(),
            ])->all(),
        ]]], 200);
    }

    private function instagramMediaInsightResponse(string $metrics)
    {
        $values = ['reach' => 500, 'views' => 900, 'total_interactions' => 52, 'likes' => 30,
            'comments' => 5, 'shares' => 3, 'saved' => 4, 'plays' => 850,
            'ig_reels_video_view_total_time' => 24000];

        return Http::response(['data' => collect(explode(',', $metrics))->map(fn ($metric) => [
            'name' => $metric, 'period' => 'lifetime', 'values' => [['value' => $values[$metric] ?? 0]],
        ])->all()], 200);
    }

    private function instagramDemographicResponse(string $breakdown)
    {
        $results = match ($breakdown) {
            'age,gender' => [['dimension_values' => ['25-34', 'F'], 'value' => 65], ['dimension_values' => ['25-34', 'M'], 'value' => 35]],
            'city' => [['dimension_values' => ['La Paz'], 'value' => 75], ['dimension_values' => ['Santa Cruz'], 'value' => 25]],
            default => [['dimension_values' => ['BO'], 'value' => 88], ['dimension_values' => ['PE'], 'value' => 12]],
        };

        return Http::response(['data' => [[
            'name' => 'follower_demographics',
            'total_value' => ['breakdowns' => [['dimension_keys' => explode(',', $breakdown), 'results' => $results]]],
        ]]], 200);
    }
}
