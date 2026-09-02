<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class AdminCampaignAnalyticsService
{
    public function __construct(private MetaCampaignAnalyticsService $metaAnalytics)
    {
    }

    public function build(Collection $campaigns, int|string $days = 30): ?array
    {
        $payloads = $campaigns->map(function ($campaign) use ($days) {
            try {
                return [
                    'campaign' => $campaign,
                    'analytics' => $this->metaAnalytics->forCampaign($campaign, $days),
                ];
            } catch (Throwable $exception) {
                report($exception);

                return ['campaign' => $campaign, 'analytics' => null];
            }
        });

        if (! $payloads->contains(fn (array $item) => $this->hasConnectedAccount($item['analytics']))) {
            return null;
        }

        return $this->aggregate($payloads, $days);
    }

    /**
     * Consolida exactamente la estructura que entrega MetaCampaignAnalyticsService
     * a la pestaña #analiticas de cada campaña.
     */
    public function aggregate(Collection $payloads, int|string $days = 30): array
    {
        $campaignMetrics = $payloads->map(function (array $item) {
            $campaign = $item['campaign'];
            $analytics = $item['analytics'];
            $summary = data_get($analytics, 'summary', []);
            $reach = $this->number(data_get($summary, 'totals.reach'));
            $interactions = $this->summaryInteractions($analytics);
            $hasConnection = $this->hasConnectedAccount($analytics);

            return [
                'id' => $campaign->id,
                'campaign' => $campaign->nombre,
                'user' => $campaign->cliente->name ?? 'Cliente sin nombre',
                'reach' => (int) round($reach ?? 0),
                'interactions' => (int) round($interactions),
                'engagement' => $reach && $reach > 0 ? round(($interactions / $reach) * 100, 2) : 0.0,
                'status' => $campaign->estado,
                'connected' => $hasConnection,
                'has_data' => $hasConnection && (($reach ?? 0) > 0 || $interactions > 0 || (int) data_get($summary, 'totals.posts', 0) > 0),
            ];
        })->values();

        $campaignsByUser = $campaignMetrics->groupBy('user')->map(function (Collection $items, string $userName) {
            $reach = (int) $items->sum('reach');
            $interactions = (int) $items->sum('interactions');

            return [
                'user' => $userName,
                'campaigns' => $items->count(),
                'reach' => $reach,
                'interactions' => $interactions,
                'engagement' => $reach > 0 ? round(($interactions / $reach) * 100, 2) : 0.0,
            ];
        })->sortByDesc('reach')->take(5)->values()->all();

        $posts = $this->uniquePosts($payloads);
        [$dailyLabels, $dailyPerformance] = $this->dailySeries($posts, $payloads, $days);
        [$topHorarios, $heatmapHours, $heatmapRows] = $this->postingTimes($posts);
        $totalReach = (int) $campaignMetrics->sum('reach');
        $totalInteractions = (int) $campaignMetrics->sum('interactions');
        $campaignReach = $campaignMetrics->sortByDesc('reach')->take(6)->map(fn (array $item) => [
            'campaign' => $item['campaign'],
            'reach' => $item['reach'],
            'engagement' => $item['engagement'],
        ])->values()->all();

        $statusLabels = [
            'activa' => 'Activa',
            'pausada' => 'Pausada',
            'finalizada' => 'Finalizada',
            'planificada' => 'Planificada',
            'revision' => 'Revisión',
            'borrador' => 'Borrador',
        ];
        $statusDistribution = $campaignMetrics->groupBy('status')->map->count()->mapWithKeys(
            fn (int $count, string $status) => [$statusLabels[$status] ?? ucfirst($status) => $count]
        )->all();
        $recommendedCampaign = $campaignMetrics
            ->where('has_data', true)
            ->where('reach', '>', 0)
            ->sortByDesc('engagement')
            ->first();

        return [
            'monthName' => $this->periodLabel($days),
            'periodDays' => $days,
            'campaignMetrics' => $campaignMetrics,
            'campaignsByUser' => $campaignsByUser,
            'dailyLabels' => $dailyLabels,
            'dailyPerformance' => $dailyPerformance,
            'campaignReach' => $campaignReach,
            'statusDistribution' => $statusDistribution,
            'heatmapHours' => $heatmapHours,
            'heatmapRows' => $heatmapRows,
            'topHorarios' => $topHorarios,
            'heatmapSummary' => $topHorarios->take(5)->map(fn (array $slot) => $slot['dia'].' '.$slot['hora'])->implode(', '),
            'heatmapModel' => 'Promedio bayesiano ponderado',
            'scoreMin' => (float) ($topHorarios->min('engagement_score') ?? 0),
            'scoreMax' => (float) ($topHorarios->max('engagement_score') ?? 0),
            'totalCampaigns' => $campaignMetrics->count(),
            'totalReach' => $totalReach,
            'totalInteractions' => $totalInteractions,
            'averageEngagement' => $totalReach > 0 ? round(($totalInteractions / $totalReach) * 100, 2) : 0.0,
            'recommendedCampaign' => $recommendedCampaign,
            'connectedCampaigns' => $campaignMetrics->where('connected', true)->count(),
            'campaignsWithData' => $campaignMetrics->where('has_data', true)->count(),
        ];
    }

    private function hasConnectedAccount(?array $analytics): bool
    {
        return collect(data_get($analytics, 'platforms', []))->contains(
            fn (array $platform) => (bool) ($platform['connected'] ?? false)
        );
    }

    private function summaryInteractions(?array $analytics): float
    {
        return collect(data_get($analytics, 'platforms', []))->sum(function (array $platform) {
            $engagement = data_get($platform, 'engagement', []);

            return collect(['reactions', 'comments', 'shares', 'saves', 'clicks'])
                ->sum(fn (string $metric) => $this->number($engagement[$metric] ?? null) ?? 0);
        });
    }

    private function uniquePosts(Collection $payloads): Collection
    {
        return $payloads->flatMap(function (array $item) {
            return collect(data_get($item['analytics'], 'platforms', []))->flatMap(
                fn (array $platform) => $platform['posts'] ?? []
            );
        })->filter(fn (array $post) => filled($post['id'] ?? null))
            ->unique(fn (array $post) => ($post['platform'] ?? 'social').':'.$post['id'])
            ->values();
    }

    private function dailySeries(Collection $posts, Collection $payloads, int|string $days): array
    {
        $labels = collect(data_get($payloads->first(fn (array $item) => is_array($item['analytics'] ?? null)), 'analytics.summary.followers.labels', []));
        if ($labels->isEmpty()) {
            $length = $days === 'all' ? 30 : min((int) $days, 90);
            $labels = collect(range($length - 1, 0))->map(fn (int $offset) => now()->subDays($offset)->toDateString());
        }

        $byDate = $posts->filter(fn (array $post) => filled($post['timestamp'] ?? null))
            ->groupBy(fn (array $post) => Carbon::parse($post['timestamp'])->setTimezone(config('app.timezone'))->toDateString())
            ->map(fn (Collection $items) => (int) round($items->sum(fn (array $post) => $this->postInteractions($post))));

        return [
            $labels->map(fn (string $date) => Carbon::parse($date)->locale('es')->translatedFormat('d M'))->values()->all(),
            $labels->map(fn (string $date) => (int) ($byDate[$date] ?? 0))->values()->all(),
        ];
    }

    private function postingTimes(Collection $posts): array
    {
        $prepared = $posts->filter(fn (array $post) => filled($post['timestamp'] ?? null))->map(function (array $post) {
            $date = Carbon::parse($post['timestamp'])->setTimezone(config('app.timezone'));
            $reach = $this->number($post['reach'] ?? $post['views'] ?? null);
            $weighted = ($this->number($post['likes'] ?? $post['reactions'] ?? null) ?? 0)
                + (($this->number($post['comments'] ?? null) ?? 0) * 2)
                + (($this->number($post['shares'] ?? null) ?? 0) * 3)
                + (($this->number($post['saves'] ?? null) ?? 0) * 3)
                + ($this->number($post['clicks'] ?? null) ?? 0);

            return ['day' => $date->dayOfWeekIso, 'hour' => $date->hour, 'score' => $reach && $reach > 0 ? ($weighted / $reach) * 100 : $weighted];
        });

        $dayNames = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $globalAverage = (float) ($prepared->avg('score') ?? 0);
        $slots = $prepared->groupBy(fn (array $item) => $item['day'].'-'.$item['hour'])->map(function (Collection $group) use ($dayNames, $globalAverage) {
            $first = $group->first();
            $samples = $group->count();
            $average = (float) $group->avg('score');

            return [
                'dia_semana' => $first['day'],
                'dia' => $dayNames[$first['day']],
                'hora' => str_pad((string) $first['hour'], 2, '0', STR_PAD_LEFT).':00',
                'samples' => $samples,
                'engagement_score' => round((($average * $samples) + ($globalAverage * 3)) / ($samples + 3), 2),
            ];
        })->sortByDesc('engagement_score')->values();

        $hours = $slots->pluck('hora')->unique()->sort()->values()->all();
        $scoreMin = (float) ($slots->min('engagement_score') ?? 0);
        $scoreMax = (float) ($slots->max('engagement_score') ?? 0);
        $range = max($scoreMax - $scoreMin, 1);
        $rows = [];
        foreach ($dayNames as $day) {
            $rows[$day] = collect($hours)->map(function (string $hour) use ($slots, $day, $scoreMin, $range) {
                $slot = $slots->first(fn (array $item) => $item['dia'] === $day && $item['hora'] === $hour);
                if (! $slot) {
                    return ['score' => null, 'normalized' => null, 'hasData' => false];
                }

                return ['score' => $slot['engagement_score'], 'normalized' => ($slot['engagement_score'] - $scoreMin) / $range, 'hasData' => true];
            })->all();
        }

        $recommended = $slots->where('samples', '>=', 2)->values();

        return [$recommended, $hours, $rows];
    }

    private function postInteractions(array $post): float
    {
        return ($this->number($post['engagement'] ?? null) ?? 0) + ($this->number($post['clicks'] ?? null) ?? 0);
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function periodLabel(int|string $days): string
    {
        return match ((string) $days) {
            '7' => 'últimos 7 días',
            '90' => 'últimos 90 días',
            '365' => 'último año',
            '730' => 'últimos 2 años',
            'all' => 'todo el historial',
            default => 'últimos 30 días',
        };
    }
}
