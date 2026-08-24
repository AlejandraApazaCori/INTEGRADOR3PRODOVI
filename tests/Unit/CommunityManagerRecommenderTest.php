<?php

namespace Tests\Unit;

use App\Models\Campania;
use App\Models\User;
use App\Services\CommunityManagerRecommender;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class CommunityManagerRecommenderTest extends TestCase
{
    public function test_it_prefers_a_manager_without_active_campaigns(): void
    {
        $available = $this->manager(1, 'Ana', []);
        $busy = $this->manager(2, 'Bruno', [
            $this->campaign('activa', '2026-09-20'),
            $this->campaign('pausada', '2026-09-25'),
        ]);

        $ranking = (new CommunityManagerRecommender())->rank(
            collect([$busy, $available]),
            Carbon::parse('2026-08-23'),
            Carbon::parse('2026-09-23'),
        );

        $this->assertSame(1, $ranking->first()['id']);
        $this->assertSame(0, $ranking->first()['active_campaigns']);
    }

    public function test_it_prefers_the_manager_whose_current_campaign_ends_sooner(): void
    {
        $releasesSoon = $this->manager(1, 'Ana', [$this->campaign('activa', '2026-08-28')]);
        $releasesLater = $this->manager(2, 'Bruno', [$this->campaign('activa', '2026-09-20')]);

        $ranking = (new CommunityManagerRecommender())->rank(
            collect([$releasesLater, $releasesSoon]),
            Carbon::parse('2026-08-23'),
            Carbon::parse('2026-09-23'),
        );

        $this->assertSame(1, $ranking->first()['id']);
        $this->assertSame(1, $ranking->first()['ending_soon']);
        $this->assertSame('28/08/2026', $ranking->first()['next_release']);
    }

    private function manager(int $id, string $name, array $campaigns): User
    {
        $manager = new User();
        $manager->forceFill(['id' => $id, 'name' => $name]);
        $manager->setRelation('campaniasComoCM', new Collection($campaigns));

        return $manager;
    }

    private function campaign(string $status, string $endsAt): Campania
    {
        return new Campania(['estado' => $status, 'fecha_fin' => $endsAt]);
    }
}
