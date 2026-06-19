<?php

declare(strict_types=1);

namespace App\Tests\Planet\ValueObject;

use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Resource\ValueObject\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanetTypeTest extends TestCase
{
    public function test_terran_is_neutral(): void
    {
        self::assertSame(1.0, PlanetType::TERRAN->getConsumptionMultiplier(ResourceType::WATER));
        self::assertSame(1.0, PlanetType::TERRAN->getConsumptionMultiplier(ResourceType::FOOD));
    }

    public function test_desert_has_water_and_food_malus(): void
    {
        self::assertSame(1.5, PlanetType::DESERT->getConsumptionMultiplier(ResourceType::WATER));
        self::assertSame(1.5, PlanetType::DESERT->getConsumptionMultiplier(ResourceType::FOOD));
    }

    public function test_ocean_has_water_bonus(): void
    {
        self::assertSame(0.5, PlanetType::OCEAN->getConsumptionMultiplier(ResourceType::WATER));
        self::assertSame(1.0, PlanetType::OCEAN->getConsumptionMultiplier(ResourceType::FOOD));
    }

    public function test_ice_easier_water_harder_food(): void
    {
        self::assertSame(0.5, PlanetType::ICE->getConsumptionMultiplier(ResourceType::WATER));
        self::assertSame(1.2, PlanetType::ICE->getConsumptionMultiplier(ResourceType::FOOD));
    }

    public function test_volcanic_malus_water_food(): void
    {
        self::assertSame(1.3, PlanetType::VOLCANIC->getConsumptionMultiplier(ResourceType::WATER));
        self::assertSame(1.2, PlanetType::VOLCANIC->getConsumptionMultiplier(ResourceType::FOOD));
    }

    public function test_terran_medium_deposits(): void
    {
        $deposits = PlanetType::TERRAN->generateDeposits(PlanetSize::MEDIUM);

        self::assertSame(500, $deposits[ResourceType::IRON_ORE->value]);
        self::assertSame(300, $deposits[ResourceType::COAL->value]);
    }

    public function test_huge_size_doubles_deposits(): void
    {
        $deposits = PlanetType::TERRAN->generateDeposits(PlanetSize::HUGE);

        self::assertSame(1000, $deposits[ResourceType::IRON_ORE->value]);
        self::assertSame(600, $deposits[ResourceType::COAL->value]);
    }

    public function test_tiny_size_halves_deposits(): void
    {
        $deposits = PlanetType::BARREN->generateDeposits(PlanetSize::TINY);

        // 1500 * 0.5 = 750, 800 * 0.5 = 400
        self::assertSame(750, $deposits[ResourceType::IRON_ORE->value]);
        self::assertSame(400, $deposits[ResourceType::COPPER_ORE->value]);
    }

    public function test_gas_giant_has_no_deposits(): void
    {
        $deposits = PlanetType::GAS_GIANT->generateDeposits(PlanetSize::HUGE);

        self::assertEmpty($deposits);
    }

    /**
     * @return array<string, array{PlanetType, ResourceType, ResourceType}>
     */
    public static function typeBiasProvider(): array
    {
        return [
            'barren_iron'  => [PlanetType::BARREN,   ResourceType::IRON_ORE,    ResourceType::COPPER_ORE],
            'desert_si'    => [PlanetType::DESERT,   ResourceType::SILICON,     ResourceType::TITANIUM_ORE],
            'volcanic_uranium' => [PlanetType::VOLCANIC, ResourceType::URANIUM_ORE, ResourceType::IRON_ORE],
        ];
    }

    #[DataProvider('typeBiasProvider')]
    public function test_each_type_has_at_least_two_deposits(PlanetType $type, ResourceType $expected1, ResourceType $expected2): void
    {
        $deposits = $type->generateDeposits(PlanetSize::MEDIUM);

        self::assertArrayHasKey($expected1->value, $deposits);
        self::assertArrayHasKey($expected2->value, $deposits);
    }
}
