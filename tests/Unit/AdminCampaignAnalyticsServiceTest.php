<?php

namespace Tests\Unit;

use App\Models\Campania;
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
        $campaignA = (object) ['id' => 1, 'nombre' => 'Campaña A', 'estado' => 'activa', 'cliente' => $client];
        $campaignB = (object) ['id' => 2, 'nombre' => 'Campaña B', 'estado' => 'finalizada', 'cliente' => $client];
        $post = [
            'id' => 'post-1', 'platform' => 'facebook', 'timestamp' => '2026-09-01T14:00:00-04:00',
            'likes' => 10, 'comments' => 2, 'shares' => 1, 'saves' => null, 'clicks' => 3,
            'reach' => 200, 'views' => 250, 'engagement' => 13,
        ];
        $analyticsA = $this->analyticsPayload(1000, 15, 4, 3, [$post]);
        $analyticsB = $this->analyticsPayload(500, 8, 2, 0, [$post]);

        $dashboard = $service->aggregate(new Collection([
            ['campaign' => $campaignA, 'analytics' => $analyticsA],
            ['campaign' => $campaignB, 'analytics' => $analyticsB],
        ]), [
            'type' => 'all',
            'since' => null,
            'until' => Carbon::parse('2026-09-30')->endOfDay(),
            'label' => 'todo el historial',
        ]);

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
        $campaign = (object) [
            'id' => 1,
            'nombre' => 'Campaña histórica',
            'estado' => 'activa',
            'cliente' => (object) ['name' => 'Cliente real'],
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
            'campaign' => $campaign,
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
        $campaign = new Campania();
        $campaign->forceFill(['id' => 9, 'nombre' => 'Campaña conectada', 'estado' => 'activa']);
        $campaign->setRelation('cliente', new User(['name' => 'Cliente conectado']));
        $meta->shouldReceive('connectedProvidersForCampaign')->once()->with($campaign)->andReturn(['instagram']);
        $meta->shouldReceive('forCampaign')->once()->with($campaign, 'all')->andReturn([
            'platforms' => [
                'facebook' => ['connected' => false, 'posts' => []],
                'instagram' => ['connected' => false, 'posts' => []],
            ],
        ]);
        $service = new AdminCampaignAnalyticsService($meta);

        $dashboard = $service->build(new Collection([$campaign]), [
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
