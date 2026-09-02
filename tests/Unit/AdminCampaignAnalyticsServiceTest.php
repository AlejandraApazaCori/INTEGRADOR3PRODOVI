<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\User;
use App\Services\AdminCampaignAnalyticsService;
use App\Services\MetaCampaignAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class AdminCampaignAnalyticsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_consolidates_real_campaign_payloads_and_deduplicates_daily_posts(): void
    {
        $service = new AdminCampaignAnalyticsService(Mockery::mock(MetaCampaignAnalyticsService::class));
        $client = (object) ['name' => 'Cliente real'];
        $companyA = (object) ['id' => 1, 'nombre_empresa' => 'Empresa A', 'usuario' => $client, 'campanias' => collect([(object) ['id' => 1]])];
        $companyB = (object) ['id' => 2, 'nombre_empresa' => 'Empresa B', 'usuario' => $client, 'campanias' => collect([(object) ['id' => 2]])];
        $campaigns = collect([(object) ['id' => 1, 'estado' => 'activa'], (object) ['id' => 2, 'estado' => 'finalizada']]);
        $post = [
            'id' => 'post-1', 'platform' => 'facebook', 'timestamp' => '2026-09-01T14:00:00-04:00',
            'likes' => 10, 'comments' => 2, 'shares' => 1, 'saves' => null, 'clicks' => 3,
            'reach' => 200, 'views' => 250, 'engagement' => 13,
        ];
        $analyticsA = $this->analyticsPayload(1000, 15, 4, 3, [$post]);
        $analyticsB = $this->analyticsPayload(500, 8, 2, 0, [$post]);

        $dashboard = $service->aggregate(new Collection([
            ['company' => $companyA, 'analytics' => $analyticsA],
            ['company' => $companyB, 'analytics' => $analyticsB],
        ]), [
            'type' => 'all',
            'since' => null,
            'until' => Carbon::parse('2026-09-30')->endOfDay(),
            'label' => 'todo el historial',
        ], $campaigns);

        $this->assertSame(2, $dashboard['totalCampaigns']);
        $this->assertSame(400, $dashboard['totalReach']);
        $this->assertSame(32, $dashboard['totalInteractions']);
        $this->assertSame(8.0, $dashboard['averageEngagement']);
        $this->assertSame(2, $dashboard['connectedCampaigns']);
        $this->assertSame(1, collect($dashboard['dailyPerformance'])->filter()->count());
        $this->assertSame(16, collect($dashboard['dailyPerformance'])->sum());
        $this->assertSame([], $dashboard['topHorarios']->all(), 'Una sola publicación no debe producir una recomendación horaria.');
    }

    public function test_it_filters_the_complete_history_by_an_exact_month(): void
    {
        $service = new AdminCampaignAnalyticsService(Mockery::mock(MetaCampaignAnalyticsService::class));
        $company = (object) [
            'id' => 1,
            'nombre_empresa' => 'Empresa histórica',
            'usuario' => (object) ['name' => 'Cliente real'],
            'campanias' => collect(),
        ];
        $septemberPost = [
            'id' => 'sep', 'platform' => 'instagram', 'timestamp' => '2026-09-10T10:00:00-04:00',
            'likes' => 10, 'comments' => 2, 'shares' => 1, 'saves' => 0, 'clicks' => 3,
            'reach' => 200, 'views' => 250, 'engagement' => 13,
        ];
        $augustPost = $septemberPost + [];
        $augustPost['id'] = 'aug';
        $augustPost['timestamp'] = '2026-08-10T10:00:00-04:00';

        $dashboard = $service->aggregate(new Collection([[
            'company' => $company,
            'analytics' => $this->analyticsPayload(9999, 99, 99, 99, [$augustPost, $septemberPost]),
        ]]), [
            'type' => 'month',
            'since' => Carbon::parse('2026-09-01')->startOfDay(),
            'until' => Carbon::parse('2026-09-30')->endOfDay(),
            'label' => 'septiembre 2026',
        ]);

        $this->assertSame(200, $dashboard['totalReach']);
        $this->assertSame(16, $dashboard['totalInteractions']);
        $this->assertSame(16, collect($dashboard['dailyPerformance'])->sum());
        $this->assertSame('septiembre 2026', $dashboard['monthName']);
    }

    public function test_a_stored_connection_keeps_the_real_panel_active_when_optional_metrics_fail(): void
    {
        $meta = Mockery::mock(MetaCampaignAnalyticsService::class);
        $company = new Empresa();
        $company->forceFill(['id' => 9, 'nombre_empresa' => 'Empresa conectada']);
        $company->setRelation('usuario', new User(['name' => 'Cliente conectado']));
        $company->setRelation('campanias', collect());
        $company->setRelation('suscripcion', null);
        $meta->shouldReceive('connectedProvidersForCompany')->once()->with($company)->andReturn(['instagram']);
        $meta->shouldReceive('forCompany')->once()->with($company, 'all')->andReturn([
            'platforms' => [
                'facebook' => ['connected' => false, 'posts' => []],
                'instagram' => ['connected' => false, 'posts' => []],
            ],
        ]);
        $service = new AdminCampaignAnalyticsService($meta);

        $dashboard = $service->build(new Collection([$company]), collect(), [
            'type' => 'all',
            'since' => null,
            'until' => Carbon::parse('2026-09-30')->endOfDay(),
            'label' => 'todo el historial',
        ]);

        $this->assertNotNull($dashboard);
        $this->assertSame(1, $dashboard['connectedCampaigns']);
        $this->assertSame(0, $dashboard['campaignsWithData']);
    }

    private function analyticsPayload(int $reach, int $reactions, int $comments, int $clicks, array $posts): array
    {
        return [
            'platforms' => [
                'facebook' => [
                    'connected' => true,
                    'engagement' => compact('reactions', 'comments', 'clicks') + ['shares' => null, 'saves' => null],
                    'posts' => $posts,
                ],
                'instagram' => ['connected' => false, 'engagement' => [], 'posts' => []],
            ],
            'summary' => [
                'totals' => ['reach' => $reach, 'posts' => count($posts)],
                'followers' => ['labels' => ['2026-09-01']],
            ],
        ];
    }
}
