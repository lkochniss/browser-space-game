<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-070: HOSPITAL gibt +20 Pop-Cap pro Level. Cap-Berechnung stackt mit
 * HQ (+25/Lvl) und HUB (+100/Lvl).
 */
final class HospitalPopCapTest extends IntegrationTestCase
{
    public function test_hospital_l1_adds_20_to_pop_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hospital = new Building(BuildingId::generate(), BuildingType::HOSPITAL, 1);
        $planet->addBuilding($hospital);

        self::assertSame(120, $planet->getPopulation()->getCap(), 'Base 100 + Hospital L1 (+20) = 120');
    }

    public function test_hospital_l3_adds_60(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hospital = new Building(BuildingId::generate(), BuildingType::HOSPITAL, 3);
        $planet->addBuilding($hospital);

        self::assertSame(160, $planet->getPopulation()->getCap(), 'Base 100 + Hospital L3 (+60) = 160');
    }

    public function test_hospital_stacks_with_hq_and_hub(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HQ, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HOSPITAL, 2));

        // Base 100 + HQ (25) + HUB (100) + Hospital L2 (40) = 265
        self::assertSame(265, $planet->getPopulation()->getCap());
    }

    public function test_pop_cap_bonus_per_level_value(): void
    {
        self::assertSame(20, BuildingType::HOSPITAL->getPopulationCapBonusPerLevel());
        self::assertSame(0, BuildingType::UNIVERSITY->getPopulationCapBonusPerLevel());
        self::assertSame(0, BuildingType::CULTURAL_CENTER->getPopulationCapBonusPerLevel());
        self::assertSame(0, BuildingType::TEMPLE->getPopulationCapBonusPerLevel());
    }
}
