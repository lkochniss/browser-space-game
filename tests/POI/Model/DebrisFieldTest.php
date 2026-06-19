<?php

declare(strict_types=1);

namespace App\Tests\POI\Model;

use App\POI\Model\DebrisField;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DebrisFieldTest extends TestCase
{
    public function test_initial_contents_persist(): void
    {
        $field = $this->makeField([
            ResourceType::DEBRIS_LOW->value => 5,
            ResourceType::DEBRIS_HIGH->value => 1,
        ]);

        self::assertSame(5, $field->getAmount(ResourceType::DEBRIS_LOW));
        self::assertSame(1, $field->getAmount(ResourceType::DEBRIS_HIGH));
        self::assertSame(0, $field->getAmount(ResourceType::DEBRIS_MEDIUM));
        self::assertSame(6, $field->getTotalAmount());
        self::assertFalse($field->isEmpty());
    }

    public function test_extract_reduces_amount(): void
    {
        $field = $this->makeField([ResourceType::DEBRIS_LOW->value => 10]);

        $taken = $field->extract(ResourceType::DEBRIS_LOW, 3);

        self::assertSame(3, $taken);
        self::assertSame(7, $field->getAmount(ResourceType::DEBRIS_LOW));
    }

    public function test_extract_more_than_available_returns_remainder(): void
    {
        $field = $this->makeField([ResourceType::DEBRIS_LOW->value => 2]);

        $taken = $field->extract(ResourceType::DEBRIS_LOW, 5);

        self::assertSame(2, $taken);
        self::assertTrue($field->isEmpty());
    }

    public function test_set_amount_with_non_debris_resource_throws(): void
    {
        $field = $this->makeField([]);

        $this->expectException(InvalidArgumentException::class);
        $field->setAmount(ResourceType::IRON_ORE, 10);
    }

    public function test_construct_with_non_debris_resource_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeField([ResourceType::IRON_ORE->value => 10]);
    }

    /**
     * @param array<string, int> $contents
     */
    private function makeField(array $contents): DebrisField
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        return new DebrisField(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Test-Debris',
            contents: $contents,
        );
    }
}
