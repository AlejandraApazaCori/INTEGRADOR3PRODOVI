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

    public function build(Collection $campaigns, array $period): ?array
    {
        $payloads = $campaigns->map(function ($campaign) {
            $connectedProviders = [];
            try {
                $connectedProviders = $this->metaAnalytics->connectedProvidersForCampaign($campaign);

                return [
                    'campaign' => $campaign,
                    'analytics' => $this->metaAnalytics->forCampaign($campaign, 'all'),
                    'connected_providers' => $connectedProviders,
                ];
            } catch (Throwable $exception) {
                report($exception);

                return [
                    'campaign' => $campaign,
                    'analytics' => null,
                    'connected_providers' => $connectedProviders,
                ];
            }
        });

        if (! $payloads->contains(fn (array $item) => $this->itemHasConnectedAccount($item))) {
            return null;
        }

        return $this->aggregate($payloads, $period);
    }

    /**
     * Consolida exactamente la estructura que entrega MetaCampaignAnalyticsService
     * a la pestaña #analiticas de cada campaña.
     */
    public function aggregate(Collection $payloads, array $period): array
    {
        $campaignMetrics = $payloads->map(function (array $item) use ($period) {
            $campaign = $item['campaign'];
            $analytics = $item['analytics'];
            $posts = $this->postsForAnalytics($analytics, $period);
            $reachValues = $posts->map(fn (array $post) => $this->number($post['reach'] ?? null))->filter(fn ($value) => $value !== null);
            $reach = $reachValues->isNotEmpty() ? (float) $reachValues->sum() : 0.0;
            $interactions = (float) $posts->sum(fn (array $post) => $this->postInteractions($post));
            $hasConnection = $this->itemHasConnectedAccount($item);

            return [
                'id' => $campaign->id,
                'campaign' => $campaign->nombre,
                'user' => $campaign->cliente->name ?? 'Cliente sin nombre',
                'reach' => (int) round($reach ?? 0),
                'interactions' => (int) round($interactions),
                'engagement' => $reach && $reach > 0 ? round(($interactions / $reach) * 100, 2) : 0.0,
                'status' => $campaign->estado,
                'connected' => $hasConnection,
                'has_data' => $hasConnection && $posts->isNotEmpty(),
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

        $posts = $this->uniquePosts($payloads, $period);
        [$dailyLabels, $dailyPerformance] = $this->dailySeries($posts, $period);
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
            'monthName' => $period['label'],
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

    private function itemHasConnectedAccount(array $item): bool
    {
        return ! empty($item['connected_providers'] ?? [])
            || $this->hasConnectedAccount($item['analytics'] ?? null);
    }

    private function uniquePosts(Collection $payloads, array $period): Collection
    {
        return $payloads->flatMap(fn (array $item) => $this->postsForAnalytics($item['analytics'], $period))
            ->unique(fn (array $post) => ($post['platform'] ?? 'social').':'.$post['id'])
            ->values();
    }

    private function postsForAnalytics(?array $analytics, array $period): Collection
    {
        return collect(data_get($analytics, 'platforms', []))->flatMap(fn (array $platform) => $platform['posts'] ?? [])
            ->filter(function (array $post) use ($period) {
                if (! filled($post['id'] ?? null) || ! filled($post['timestamp'] ?? null)) {
                    return false;
                }
                $publishedAt = Carbon::parse($post['timestamp'])->setTimezone(config('app.timezone'));

                return (! $period['since'] || $publishedAt->greaterThanOrEqualTo($period['since']))
                    && (! $period['until'] || $publishedAt->lessThanOrEqualTo($period['until']));
            })->unique(fn (array $post) => ($post['platform'] ?? 'social').':'.$post['id'])->values();
    }

    private function dailySeries(Collection $posts, array $period): array
    {
        $since = $period['since']?->copy();
        $until = ($period['until'] ?? now())->copy();
        if (! $since) {
            $firstTimestamp = $posts->pluck('timestamp')->filter()->min();
            $since = $firstTimestamp ? Carbon::parse($firstTimestamp)->setTimezone(config('app.timezone'))->startOfMonth() : now()->startOfMonth();
        }
        $useMonths = $period['type'] === 'all' || $period['type'] === 'year' || $since->diffInDays($until) > 120;
        $bucketFormat = $useMonths ? 'Y-m' : 'Y-m-d';
        $labelFormat = $useMonths ? 'M Y' : 'd M';
        $cursor = $useMonths ? $since->copy()->startOfMonth() : $since->copy()->startOfDay();
        $limit = $useMonths ? $until->copy()->startOfMonth() : $until->copy()->startOfDay();
        $buckets = collect();
        while ($cursor->lessThanOrEqualTo($limit)) {
            $buckets->push($cursor->copy());
            $useMonths ? $cursor->addMonth() : $cursor->addDay();
        }

        $byDate = $posts->filter(fn (array $post) => filled($post['timestamp'] ?? null))
            ->groupBy(fn (array $post) => Carbon::parse($post['timestamp'])->setTimezone(config('app.timezone'))->format($bucketFormat))
            ->map(fn (Collection $items) => (int) round($items->sum(fn (array $post) => $this->postInteractions($post))));

        return [
            $buckets->map(fn (Carbon $date) => $date->locale('es')->translatedFormat($labelFormat))->values()->all(),
            $buckets->map(fn (Carbon $date) => (int) ($byDate[$date->format($bucketFormat)] ?? 0))->values()->all(),
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

}
