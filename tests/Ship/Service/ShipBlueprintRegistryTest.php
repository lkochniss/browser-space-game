<?php

declare(strict_types=1);

namespace App\Tests\Ship\Service;

use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\ShipBlueprintNotFoundException;
use App\Ship\Service\ShipBlueprintRegistry;
use App\Ship\ValueObject\ShipClass;
use PHPUnit\Framework\TestCase;

/**
 * T-102 Blueprint-Registry liefert hardcoded Stats für alle 15 Combat-Klassen.
 * Mk II/III via Q1-Skalierung (Stats × 1.5, Cost × 3 pro Tier).
 */
final class ShipBlueprintRegistryTest extends TestCase
{
    public function test_all_15_combat_classes_registered(): void
    {
        $reg = new ShipBlueprintRegistry();

        foreach (ShipClass::cases() as $class) {
            $bp = $reg->get($class);
            self::assertSame($class, $bp->class);
            self::assertGreaterThan(0, $bp->hp);
            self::assertGreaterThan(0, $bp->damage);
            self::assertGreaterThan(0, $bp->shieldCapacity);
            self::assertGreaterThan(0, $bp->buildDurationSeconds);
            self::assertNotEmpty($bp->buildCost);
        }
        self::assertCount(15, $reg->all());
    }

    public function test_frigate_mk1_base_stats(): void
    {
        $bp = (new ShipBlueprintRegistry())->get(ShipClass::FRIGATE_MK1);

        self::assertSame(1000, $bp->hp);
        self::assertSame(200, $bp->damage);
        self::assertSame(300, $bp->shieldCapacity);
        self::assertSame(30, $bp->populationCost);
        self::assertSame(6 * 3600, $bp->buildDurationSeconds);
        self::assertSame(30, $bp->escapePodChance);
        self::assertSame(500, $bp->buildCost[ResourceType::STEEL->value]);
    }

    public function test_mk2_scales_15x_stats_3x_cost(): void
    {
        $reg = new ShipBlueprintRegistry();
        $mk1 = $reg->get(ShipClass::FRIGATE_MK1);
        $mk2 = $reg->get(ShipClass::FRIGATE_MK2);

        self::assertSame((int) ceil($mk1->hp * 1.5), $mk2->hp);
        self::assertSame((int) ceil($mk1->damage * 1.5), $mk2->damage);
        self::assertSame((int) ceil($mk1->shieldCapacity * 1.5), $mk2->shieldCapacity);
        self::assertSame(
            $mk1->buildCost[ResourceType::STEEL->value] * 3,
            $mk2->buildCost[ResourceType::STEEL->value],
        );
    }

    public function test_mk3_scales_cumulatively(): void
    {
        $reg = new ShipBlueprintRegistry();
        $mk1 = $reg->get(ShipClass::FRIGATE_MK1);
        $mk3 = $reg->get(ShipClass::FRIGATE_MK3);

        // Mk III = Mk I × 2.25 stats × 9 cost
        self::assertSame((int) ceil($mk1->hp * 2.25), $mk3->hp);
        self::assertSame(
            $mk1->buildCost[ResourceType::STEEL->value] * 9,
            $mk3->buildCost[ResourceType::STEEL->value],
        );
    }

    public function test_escape_pod_chance_per_family(): void
    {
        $reg = new ShipBlueprintRegistry();
        self::assertSame(30, $reg->get(ShipClass::FRIGATE_MK1)->escapePodChance);
        self::assertSame(50, $reg->get(ShipClass::DESTROYER_MK2)->escapePodChance);
        self::assertSame(65, $reg->get(ShipClass::CRUISER_MK3)->escapePodChance);
        self::assertSame(80, $reg->get(ShipClass::BATTLESHIP_MK1)->escapePodChance);
        self::assertSame(70, $reg->get(ShipClass::CARRIER_MK1)->escapePodChance);
    }

    public function test_required_shipyard_level_per_family(): void
    {
        self::assertSame(1, ShipClass::FRIGATE_MK1->getRequiredShipyardLevel());
        self::assertSame(3, ShipClass::DESTROYER_MK1->getRequiredShipyardLevel());
        self::assertSame(5, ShipClass::CRUISER_MK1->getRequiredShipyardLevel());
        self::assertSame(8, ShipClass::BATTLESHIP_MK1->getRequiredShipyardLevel());
        self::assertSame(10, ShipClass::CARRIER_MK1->getRequiredShipyardLevel());
    }

    public function test_required_research_slug(): void
    {
        self::assertNull(ShipClass::FRIGATE_MK1->getRequiredResearchSlug());
        self::assertSame('frigate_mk2', ShipClass::FRIGATE_MK2->getRequiredResearchSlug());
        self::assertSame('battleship_mk3', ShipClass::BATTLESHIP_MK3->getRequiredResearchSlug());
    }
}
