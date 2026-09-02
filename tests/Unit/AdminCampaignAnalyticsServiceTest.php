<?php

namespace Tests\Unit;

use App\Services\AdminCampaignAnalyticsService;
use App\Services\MetaCampaignAnalyticsService;
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
        ]), 30);

        $this->assertSame(2, $dashboard['totalCampaigns']);
        $this->assertSame(1500, $dashboard['totalReach']);
        $this->assertSame(32, $dashboard['totalInteractions']);
        $this->assertSame(2.13, $dashboard['averageEngagement']);
        $this->assertSame(2, $dashboard['connectedCampaigns']);
        $this->assertSame(1, collect($dashboard['dailyPerformance'])->filter()->count());
        $this->assertSame(16, collect($dashboard['dailyPerformance'])->sum());
        $this->assertSame([], $dashboard['topHorarios']->all(), 'Una sola publicación no debe producir una recomendación horaria.');
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
