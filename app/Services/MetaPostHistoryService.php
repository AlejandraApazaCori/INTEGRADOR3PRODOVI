<?php

namespace App\Services;

use App\Models\SocialAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetaPostHistoryService
{
    public function capture(array $accounts, array $analytics): void
    {
        $observed = Carbon::parse($analytics['generated_at'])->utc();
        foreach ($accounts as $account) {
            if (! $account->exists || ! in_array($account->provider, ['facebook_page', 'instagram'], true)) {
                continue;
            }
            $network = $account->provider === 'facebook_page' ? 'facebook' : 'instagram';
            $platform = $analytics['platforms'][$network] ?? [];
            if ((string) data_get($platform, 'account.id') !== (string) $account->provider_user_id) {
                continue;
            }
            $rows = [];
            foreach ($platform['posts'] ?? [] as $post) {
                if (($post['prediction_metrics_available'] ?? false) !== true) {
                    continue;
                }
                $published = Carbon::parse($post['timestamp'])->utc();
                if ($published->greaterThan($observed)) {
                    continue;
                }
                $rows[] = [
                    'social_account_id' => $account->id, 'platform' => $network,
                    'account_id' => (string) $account->provider_user_id, 'post_id' => $post['id'],
                    'published_at' => $published->format('Y-m-d H:i:s'), 'observed_at' => $observed->format('Y-m-d H:i:s'),
                    'likes' => (int) $post['likes'], 'comments' => (int) $post['comments'],
                ];
            }
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('meta_post_snapshots')->insertOrIgnore($chunk);
            }
        }
    }

    public function latest(SocialAccount $account): array
    {
        $scope = DB::table('meta_post_snapshots')
            ->where('social_account_id', $account->id)
            ->where('account_id', $account->provider_user_id)
            ->where('observed_at', '<=', now('UTC')->format('Y-m-d H:i:s'));
        $latest = (clone $scope)->select('post_id')->selectRaw('MAX(observed_at) as latest_observed_at')->groupBy('post_id');

        return $scope->joinSub($latest, 'latest', fn ($join) => $join
            ->on('meta_post_snapshots.post_id', '=', 'latest.post_id')
            ->on('meta_post_snapshots.observed_at', '=', 'latest.latest_observed_at'))
            ->select('meta_post_snapshots.*')->orderBy('published_at')->get()
            ->map(fn ($row) => $this->datasetRow($row))->values()->all();
    }

    public function datasetRow(object $row, string $protocol = 'snapshot_variable_age'): array
    {
        return [
            'platform' => $row->platform, 'account_id' => $row->account_id, 'post_id' => $row->post_id,
            'published_at' => Carbon::parse($row->published_at, 'UTC')->toIso8601String(),
            'likes' => (int) $row->likes, 'comments' => (int) $row->comments,
            'metrics_observed_at' => Carbon::parse($row->observed_at, 'UTC')->toIso8601String(),
            'source' => 'meta_export', 'measurement_protocol' => $protocol,
        ];
    }
}
