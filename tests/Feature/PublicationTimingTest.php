<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\LstmInferenceService;
use App\Services\MetaCampaignAnalyticsService;
use App\Services\MetaPostHistoryService;
use App\Services\PublicationTimingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicationTimingTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_keeps_missing_metrics_out_and_never_mixes_accounts(): void
    {
        $a = $this->account('page-a');
        $b = $this->account('page-b');
        $history = app(MetaPostHistoryService::class);
        $payload = $this->analytics($a, 7);
        $payload['platforms']['facebook']['posts'][1]['prediction_metrics_available'] = false;
        $history->capture([$a], $payload);
        $history->capture([$a], $payload); // Mismo snapshot: no duplicar.
        $history->capture([$b], $this->analytics($b, 70));
        $this->assertCount(2, $history->latest($a));
        $this->assertCount(3, $history->latest($b));
        $this->assertSame('page-a', $history->latest($a)[0]['account_id']);
        $this->assertSame(7, $history->latest($a)[0]['likes']);
        $this->assertSame(70, $history->latest($b)[0]['likes']);
        $this->assertDatabaseCount('meta_post_snapshots', 5);
    }

    public function test_predictions_use_real_account_history_without_credentials_and_future_dates(): void
    {
        Cache::flush();
        $account = $this->account('page-a');
        $payload = $this->analytics($account, 9);
        app(MetaPostHistoryService::class)->capture([$account], $payload);
        $this->mock(MetaCampaignAnalyticsService::class, fn ($mock) => $mock->shouldReceive('forPublishingAccounts')->once()->andReturn($payload));
        $this->mock(LstmInferenceService::class, function ($mock) {
            $mock->shouldReceive('predict')->once()->andReturnUsing(function ($input) {
                $this->assertCount(168, $input['candidates']);
                $this->assertTrue(\Carbon\Carbon::parse($input['candidates'][0])->greaterThan(now()));
                $this->assertSame('page-a', $input['accounts'][0]['account_id']);
                $this->assertSame(9, $input['accounts'][0]['posts'][0]['likes']);
                $this->assertStringNotContainsString('secret-token', json_encode($input));

                return ['platforms' => ['facebook' => [
                    'account_id' => 'page-a', 'status' => 'ok', 'experimental' => true,
                    'slots' => [['timestamp' => $input['candidates'][0], 'predicted_score' => 42, 'historical_score' => 38, 'samples' => 2]],
                ]]];
            });
        });
        $result = app(PublicationTimingService::class)->forAccounts(['facebook' => $account]);
        $this->assertSame('ok', $result['platforms']['facebook']['status']);
        $this->assertSame(42, $result['platforms']['facebook']['top'][0]['predicted_score']);
        $this->assertTrue($result['platforms']['facebook']['experimental']);
        $this->assertSame('not_connected', $result['platforms']['instagram']['status']);
    }

    public function test_model_failure_returns_no_fabricated_prediction(): void
    {
        Cache::flush();
        $account = $this->account('page-a');
        $payload = $this->analytics($account, 9);
        app(MetaPostHistoryService::class)->capture([$account], $payload);
        $this->mock(MetaCampaignAnalyticsService::class, fn ($mock) => $mock->shouldReceive('forPublishingAccounts')->andReturn($payload));
        $this->mock(LstmInferenceService::class, fn ($mock) => $mock->shouldReceive('predict')->andThrow(new \RuntimeException('No model')));
        $result = app(PublicationTimingService::class)->forAccounts(['facebook' => $account]);
        $this->assertSame('unavailable', $result['platforms']['facebook']['status']);
        $this->assertSame([], $result['platforms']['facebook']['slots']);
        $this->assertSame([], $result['platforms']['facebook']['top']);
    }

    public function test_no_metrics_means_no_inference(): void
    {
        $account = $this->account('page-a');
        $this->mock(MetaCampaignAnalyticsService::class, fn ($mock) => $mock->shouldReceive('forPublishingAccounts')->andReturn($this->analytics($account, 9)));
        $this->mock(LstmInferenceService::class, fn ($mock) => $mock->shouldNotReceive('predict'));
        $result = app(PublicationTimingService::class)->forAccounts(['facebook' => $account]);
        $this->assertSame('insufficient_data', $result['platforms']['facebook']['status']);
    }

    public function test_export_does_not_backdate_current_snapshots_as_fixed_age_measurements(): void
    {
        $account = $this->account('page-a');
        app(MetaPostHistoryService::class)->capture([$account], $this->analytics($account, 9));
        $this->artisan('meta:export-training-history')->assertFailed();
        $this->assertDatabaseCount('meta_post_snapshots', 3);
    }

    private function account(string $id): SocialAccount
    {
        return User::factory()->create()->socialAccounts()->create([
            'provider' => 'facebook_page', 'provider_user_id' => $id, 'access_token' => 'secret-token',
        ]);
    }

    private function analytics(SocialAccount $account, int $likes): array
    {
        return ['generated_at' => now()->toIso8601String(), 'errors' => [], 'platforms' => ['facebook' => [
            'account' => ['id' => $account->provider_user_id],
            'posts' => collect(range(1, 3))->map(fn ($i) => [
                'id' => 'post-'.$i, 'timestamp' => now()->subDays(10 + $i)->toIso8601String(),
                'likes' => $likes, 'comments' => 2, 'prediction_metrics_available' => true,
            ])->all(),
            'best_posting_times' => ['best' => []],
        ]]];
    }
}
