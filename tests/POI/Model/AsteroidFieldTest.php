<?php

declare(strict_types=1);

namespace App\Tests\POI\Model;

use App\POI\Model\AsteroidField;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AsteroidFieldTest extends TestCase
{
    public function test_empty_field_has_zero_total(): void
    {
        $field = $this->makeField([]);

        self::assertSame(0, $field->getTotalAmount());
        self::assertTrue($field->isEmpty());
        self::assertSame([], $field->getContents());
    }

    public function test_initial_contents_persist(): void
    {
        $field = $this->makeField([
            ResourceType::IRON_ORE->value => 1500,
            ResourceType::COPPER_ORE->value => 800,
        ]);

        self::assertSame(1500, $field->getAmount(ResourceType::IRON_ORE));
        self::assertSame(800, $field->getAmount(ResourceType::COPPER_ORE));
        self::assertSame(0, $field->getAmount(ResourceType::URANIUM_ORE));
        self::assertSame(2300, $field->getTotalAmount());
        self::assertFalse($field->isEmpty());
    }

    public function test_extract_returns_taken_amount(): void
    {
        $field = $this->makeField([ResourceType::IRON_ORE->value => 1000]);

        $taken = $field->extract(ResourceType::IRON_ORE, 300);

        self::assertSame(300, $taken);
        self::assertSame(700, $field->getAmount(ResourceType::IRON_ORE));
    }

    public function test_extract_more_than_available_returns_remainder(): void
    {
        $field = $this->makeField([ResourceType::IRON_ORE->value => 200]);

        $taken = $field->extract(ResourceType::IRON_ORE, 500);

        self::assertSame(200, $taken);
        self::assertSame(0, $field->getAmount(ResourceType::IRON_ORE));
        self::assertTrue($field->isEmpty());
    }

    public function test_extract_zero_throws(): void
    {
        $field = $this->makeField([ResourceType::IRON_ORE->value => 100]);

        $this->expectException(InvalidArgumentException::class);
        $field->extract(ResourceType::IRON_ORE, 0);
    }

    public function test_set_amount_to_zero_removes_key(): void
    {
        $field = $this->makeField([ResourceType::IRON_ORE->value => 100]);
        $field->setAmount(ResourceType::IRON_ORE, 0);

        self::assertSame(0, $field->getAmount(ResourceType::IRON_ORE));
        self::assertSame([], $field->getContents());
    }

    public function test_set_negative_amount_throws(): void
    {
        $field = $this->makeField([]);

        $this->expectException(InvalidArgumentException::class);
        $field->setAmount(ResourceType::IRON_ORE, -10);
    }

    /**
     * @param array<string, int> $contents
     */
    private function makeField(array $contents): AsteroidField
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        return new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Test-Field',
            contents: $contents,
        );
    }
}
