<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicationTimingService
{
    public function __construct(
        private MetaCampaignAnalyticsService $analytics,
        private MetaPostHistoryService $history,
        private LstmInferenceService $inference,
    ) {}

    public function forAccounts(array $accounts): array
    {
        $accounts = array_filter($accounts);
        $now = now(config('app.timezone'));
        // Horas completas futuras, dejando tiempo para revisar y guardar la programación.
        $start = $now->copy()->addMinutes(15)->startOfHour()->addHour();
        $candidates = collect(range(0, 167))->map(fn ($offset) => $start->copy()->addHours($offset)->toIso8601String())->all();
        try {
            $analytics = $this->analytics->forPublishingAccounts($accounts, config('lstm.history_days'));
        } catch (\Throwable $e) {
            Log::warning('No se pudo actualizar el histórico para LSTM.');
            $analytics = ['generated_at' => $now->toIso8601String(), 'platforms' => [], 'errors' => ['unavailable']];
        }
        $platforms = [];
        $inputs = [];
        foreach (['facebook', 'instagram'] as $network) {
            $account = $accounts[$network] ?? null;
            if (! $account) {
                $platforms[$network] = ['status' => 'not_connected', 'slots' => [], 'top' => []];

                continue;
            }
            $posts = $this->history->latest($account);
            $platforms[$network] = [
                'account_id' => (string) $account->provider_user_id,
                'account_name' => $account->display_name ?: $account->username ?: ucfirst($network),
                'history_count' => count($posts), 'status' => 'insufficient_data', 'slots' => [], 'top' => [],
                'historical_best' => data_get($analytics, "platforms.{$network}.best_posting_times.best", []),
                'generated_at' => collect($posts)->max('metrics_observed_at') ?: $analytics['generated_at'],
            ];
            if ($posts !== []) {
                $inputs[] = ['platform' => $network, 'account_id' => (string) $account->provider_user_id, 'posts' => $posts];
            }
        }
        if ($inputs !== []) {
            $payload = ['accounts' => $inputs, 'candidates' => $candidates];
            $fingerprint = @file_get_contents(config('lstm.models').'/archive_sha256.txt') ?: 'missing';
            $key = 'publication-lstm:v1:'.hash('sha256', json_encode([$payload, $fingerprint]));
            try {
                $prediction = Cache::remember($key, now()->addMinutes(15), fn () => $this->inference->predict($payload));
                foreach ($prediction['platforms'] as $network => $result) {
                    if (! isset($platforms[$network]) || ($result['account_id'] ?? null) !== $platforms[$network]['account_id']) {
                        throw new \RuntimeException('La respuesta no corresponde a la cuenta solicitada.');
                    }
                    $slots = collect($result['slots'] ?? [])->filter(fn ($slot) => in_array($slot['timestamp'] ?? '', $candidates, true)
                        && is_numeric($slot['predicted_score'] ?? null)
                        && is_finite((float) $slot['predicted_score'])
                        && Carbon::parse($slot['timestamp'])->greaterThan(now()->addMinutes(5))
                    )->values();
                    $platforms[$network] = array_merge($platforms[$network], $result, [
                        'slots' => $slots->all(),
                        'top' => $slots->sortByDesc('predicted_score')->take(5)->values()->all(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Predicción LSTM no disponible.', ['reason' => $e->getMessage()]);
                foreach ($inputs as $input) {
                    $platforms[$input['platform']]['status'] = 'unavailable';
                    $platforms[$input['platform']]['slots'] = [];
                    $platforms[$input['platform']]['top'] = [];
                }
            }
        }

        return [
            'timezone' => config('app.timezone'), 'generated_at' => $now->toIso8601String(),
            'unit' => 'Puntaje: reacciones/me gusta + 2 × comentarios',
            'platforms' => $platforms,
            'has_meta_errors' => ! empty($analytics['errors']),
        ];
    }
}
