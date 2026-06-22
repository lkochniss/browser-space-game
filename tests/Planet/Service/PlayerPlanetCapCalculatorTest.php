<?php

declare(strict_types=1);

namespace App\Tests\Planet\Service;

use App\Planet\Model\Planet;
use App\Planet\Service\PlayerPlanetCapCalculator;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\PlayerResearch;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-101: PlayerPlanetCapCalculator-Test.
 *
 * Formel: min(HARD_CAP=10, BASE_CAP=5 + logistics_1.level)
 */
final class PlayerPlanetCapCalculatorTest extends IntegrationTestCase
{
    public function test_base_cap_without_research(): void
    {
        $player = $this->seedPlayer();

        self::assertSame(5, $this->calculator()->compute($player));
    }

    public function test_logistics_l1_extends_cap_to_6(): void
    {
        $player = $this->seedPlayer();
        $this->grantLogistics($player, level: 1);

        self::assertSame(6, $this->calculator()->compute($player));
    }

    public function test_logistics_l3_extends_cap_to_8(): void
    {
        $player = $this->seedPlayer();
        $this->grantLogistics($player, level: 3);

        self::assertSame(8, $this->calculator()->compute($player));
    }

    public function test_hard_cap_caps_at_10(): void
    {
        // Hypothetisches Logistics-Lvl > 3 (zukünftiger Tier-Branch) — Cap bei 10
        $player = $this->seedPlayer();
        $this->grantLogistics($player, level: 99);

        self::assertSame(10, $this->calculator()->compute($player));
    }

    public function test_current_usage_counts_planets(): void
    {
        $player = $this->seedPlayer();
        $player->claimPlanet(Planet::generatePlanet(PlanetId::generate()));
        $this->em->flush();

        self::assertSame(2, $this->calculator()->currentUsage($player));
    }

    private function seedPlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $player->claimPlanet(Planet::generatePlanet(PlanetId::generate()));
        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }

    private function grantLogistics(Player $player, int $level): void
    {
        $research = PlayerResearch::generate($player, 'logistics_1', $level);
        $this->em->persist($research);
        $this->em->flush();
    }

    private function calculator(): PlayerPlanetCapCalculator
    {
        return self::getContainer()->get(PlayerPlanetCapCalculator::class);
    }
}
