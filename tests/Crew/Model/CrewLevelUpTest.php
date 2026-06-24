<?php

declare(strict_types=1);

namespace App\Tests\Crew\Model;

use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use PHPUnit\Framework\TestCase;

/**
 * T-104a Crew-Level-Up via XP-Thresholds (pure unit, kein DB).
 */
final class CrewLevelUpTest extends TestCase
{
    public function test_xp_threshold_table(): void
    {
        self::assertSame(0, Crew::xpThresholdForLevel(1));
        self::assertSame(100, Crew::xpThresholdForLevel(2));
        self::assertSame(250, Crew::xpThresholdForLevel(3));
        self::assertSame(5000, Crew::xpThresholdForLevel(10));
    }

    public function test_add_xp_below_threshold_no_level_up(): void
    {
        $crew = $this->makeCrew();
        $crew->addXp(50);

        self::assertSame(1, $crew->getLevel());
        self::assertSame(50, $crew->getXp());
    }

    public function test_add_xp_above_threshold_levels_up(): void
    {
        $crew = $this->makeCrew();
        $crew->addXp(100);

        self::assertSame(2, $crew->getLevel());
        self::assertSame(100, $crew->getXp());
    }

    public function test_big_xp_chains_level_ups(): void
    {
        $crew = $this->makeCrew();
        $crew->addXp(5000);

        self::assertSame(10, $crew->getLevel());
        self::assertSame(5000, $crew->getXp());
    }

    public function test_cannot_exceed_max_level(): void
    {
        $crew = $this->makeCrew();
        $crew->addXp(999_999);

        self::assertSame(10, $crew->getLevel());
    }

    public function test_stats_multiplier_only_when_assigned(): void
    {
        $crew = $this->makeCrew();
        $crew->addXp(1000); // L5

        // IDLE-Crew gibt KEINEN Stats-Bonus
        self::assertSame(1.0, $crew->getStatsMultiplier());
    }

    public function test_stats_multiplier_3_percent_per_level_when_assigned(): void
    {
        // Crew manuell auf ASSIGNED-Status + L10
        $player = new Player(PlayerId::generate());
        $crew = new Crew(
            CrewId::generate(), $player, CrewType::CAPTAIN, CrewStatus::ASSIGNED, 10,
        );

        // L10 = 1 + 0.03 × 10 = 1.30
        self::assertEqualsWithDelta(1.30, $crew->getStatsMultiplier(), 0.0001);
    }

    private function makeCrew(): Crew
    {
        $player = new Player(PlayerId::generate());
        return new Crew(
            CrewId::generate(), $player, CrewType::CAPTAIN, CrewStatus::IDLE, 1, 0,
        );
    }
}
