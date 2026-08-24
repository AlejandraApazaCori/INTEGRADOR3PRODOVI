<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CommunityManagerRecommender
{
    /**
     * Ordena los responsables desde la carga proyectada más baja.
     */
    public function rank(Collection $managers, CarbonInterface $startsAt, CarbonInterface $endsAt): Collection
    {
        $start = Carbon::parse($startsAt)->startOfDay();
        $end = Carbon::parse($endsAt)->startOfDay();
        $horizonDays = max(1, (int) $start->diffInDays($end) + 1);

        return $managers
            ->map(function ($manager) use ($start, $end, $horizonDays) {
                $campaigns = collect($manager->campaniasComoCM)
                    ->filter(fn ($campaign) => in_array($campaign->estado, ['activa', 'pausada'], true)
                        && Carbon::parse($campaign->fecha_fin)->endOfDay()->greaterThanOrEqualTo($start));

                $committedDays = $campaigns->sum(function ($campaign) use ($start, $end) {
                    $campaignEnd = Carbon::parse($campaign->fecha_fin)->startOfDay()->min($end);

                    return $campaignEnd->lessThan($start)
                        ? 0
                        : (int) $start->diffInDays($campaignEnd) + 1;
                });

                $endingSoon = $campaigns->filter(fn ($campaign) => Carbon::parse($campaign->fecha_fin)
                    ->startOfDay()->betweenIncluded($start, $start->copy()->addDays(14)))->count();
                $nextRelease = $campaigns->min(fn ($campaign) => Carbon::parse($campaign->fecha_fin)->timestamp);
                $averageConcurrentLoad = round($committedDays / $horizonDays, 2);

                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'active_campaigns' => $campaigns->count(),
                    'ending_soon' => $endingSoon,
                    'average_load' => $averageConcurrentLoad,
                    'next_release' => $nextRelease ? Carbon::createFromTimestamp($nextRelease)->format('d/m/Y') : null,
                    'score' => round(($campaigns->count() * 40) + ($averageConcurrentLoad * 50) - ($endingSoon * 5), 2),
                ];
            })
            ->sortBy([
                ['score', 'asc'],
                ['active_campaigns', 'asc'],
                ['average_load', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }
}
