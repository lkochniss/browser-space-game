<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Model\Building;
use App\Building\Service\BuildQueueCapCalculator;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\PlayerResearch;
use App\Research\Repository\PlayerResearchRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildQueueCapCalculatorTest extends TestCase
{
    public function test_no_player_returns_planet_only(): void
    {
        $planet = $this->makePlanet(hqLevel: 0);
        $calc = $this->makeCalc([]);

        self::assertSame(3, $calc->compute($planet, null, new DateTimeImmutable()));
    }

    public function test_no_logistics_no_bonus(): void
    {
        $planet = $this->makePlanet(hqLevel: 5);
        $calc = $this->makeCalc([]);

        // HQ L5 = +1 → 4
        self::assertSame(4, $calc->compute($planet, $this->makePlayer(), new DateTimeImmutable()));
    }

    public function test_logistics_l1_adds_one_slot(): void
    {
        $planet = $this->makePlanet(hqLevel: 0);
        $calc = $this->makeCalc(['logistics_1' => 1]);

        self::assertSame(4, $calc->compute($planet, $this->makePlayer(), new DateTimeImmutable()));
    }

    public function test_logistics_l3_adds_three_slots(): void
    {
        $planet = $this->makePlanet(hqLevel: 0);
        $calc = $this->makeCalc(['logistics_1' => 3]);

        self::assertSame(6, $calc->compute($planet, $this->makePlayer(), new DateTimeImmutable()));
    }

    public function test_hq_l10_plus_logistics_l3_capped_at_8(): void
    {
        // HQ L10 = +2 → planet-cap 5. + Logistics L3 = +3 → 8 (cap).
        $planet = $this->makePlanet(hqLevel: 10);
        $calc = $this->makeCalc(['logistics_1' => 3]);

        self::assertSame(8, $calc->compute($planet, $this->makePlayer(), new DateTimeImmutable()));
    }

    public function test_hq_l25_plus_logistics_l3_capped_at_8(): void
    {
        // HQ alone already at 8 (cap). Logistics darf nicht überlaufen.
        $planet = $this->makePlanet(hqLevel: 25);
        $calc = $this->makeCalc(['logistics_1' => 3]);

        self::assertSame(8, $calc->compute($planet, $this->makePlayer(), new DateTimeImmutable()));
    }

    private function makePlanet(int $hqLevel): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        if ($hqLevel > 0) {
            $hq = new Building(BuildingId::generate(), BuildingType::HQ, $hqLevel);
            $hq->setFinishedAt(new DateTimeImmutable('-1 minute'));
            $planet->addBuilding($hq);
        }

        return $planet;
    }

    private function makePlayer(): Player
    {
        return new Player(PlayerId::generate());
    }

    /**
     * @param array<string, int> $researchLevels
     */
    private function makeCalc(array $researchLevels): BuildQueueCapCalculator
    {
        $repo = $this->createMock(PlayerResearchRepository::class);
        $repo->method('findOneByPlayerAndSlug')
            ->willReturnCallback(function ($player, $slug) use ($researchLevels) {
                if (!isset($researchLevels[$slug])) {
                    return null;
                }
                $entry = $this->createMock(PlayerResearch::class);
                $entry->method('getLevel')->willReturn($researchLevels[$slug]);

                return $entry;
            });

        return new BuildQueueCapCalculator($repo);
    }
}
