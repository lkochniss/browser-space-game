<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use PHPUnit\Framework\TestCase;

/**
 * T-068 Defense-Stats: live aus operational Buildings × Level.
 */
final class PlanetDefenseStatsTest extends TestCase
{
    public function test_empty_planet_returns_zero_stats(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $stats = $planet->getDefenseStats();

        self::assertSame(0, $stats->shieldHp);
        self::assertSame(0, $stats->shieldHpMax);
        self::assertSame(0, $stats->turretDamage);
        self::assertSame(0, $stats->sensorRange);
        self::assertSame(0, $stats->aaDamage);
    }

    public function test_planetary_shield_l3_provides_15000_shield_hp(): void
    {
        $planet = $this->planetWithDefense([
            [BuildingType::PLANETARY_SHIELD, 3],
        ]);
        $stats = $planet->getDefenseStats();

        self::assertSame(15000, $stats->shieldHpMax);
        self::assertSame(15000, $stats->shieldHp);
    }

    public function test_multiple_turrets_stack_damage(): void
    {
        $planet = $this->planetWithDefense([
            [BuildingType::DEFENSE_TURRET, 2],
            [BuildingType::DEFENSE_TURRET, 3],
        ]);

        // 2×500 + 3×500 = 2500
        self::assertSame(2500, $planet->getDefenseStats()->turretDamage);
    }

    public function test_sensor_range_takes_max_not_sum(): void
    {
        $planet = $this->planetWithDefense([
            [BuildingType::SENSOR_ARRAY, 3],
        ]);

        self::assertSame(3, $planet->getDefenseStats()->sensorRange);
    }

    public function test_aa_battery_stacks_damage(): void
    {
        $planet = $this->planetWithDefense([
            [BuildingType::AA_BATTERY, 2],
            [BuildingType::AA_BATTERY, 1],
        ]);

        self::assertSame(900, $planet->getDefenseStats()->aaDamage);
    }

    public function test_damaged_building_excluded(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $b = new Building(BuildingId::generate(), BuildingType::DEFENSE_TURRET, 3);
        $b->restoreFullHp();
        $b->takeDamage($b->computeMaxHp()); // → 0 HP
        $planet->addBuilding($b);

        self::assertSame(0, $planet->getDefenseStats()->turretDamage);
    }

    public function test_unfinished_building_excluded(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $b = new Building(BuildingId::generate(), BuildingType::DEFENSE_TURRET, 3);
        $b->restoreFullHp();
        $b->setFinishedAt((new \DateTimeImmutable())->modify('+1 hour'));
        $planet->addBuilding($b);

        $now = new \DateTimeImmutable();
        self::assertSame(0, $planet->getDefenseStats($now)->turretDamage);
    }

    /**
     * @param list<array{0:BuildingType,1:int}> $defs
     */
    private function planetWithDefense(array $defs): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        foreach ($defs as [$type, $level]) {
            $b = new Building(BuildingId::generate(), $type, $level);
            $b->restoreFullHp();
            $planet->addBuilding($b);
        }

        return $planet;
    }
}
