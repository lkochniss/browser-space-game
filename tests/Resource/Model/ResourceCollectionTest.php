<?php

declare(strict_types=1);

namespace App\Tests\Resource\Model;

use App\Resource\Model\Resource;
use App\Resource\Model\ResourceCollection;
use App\Resource\ValueObject\ResourceType;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ResourceCollectionTest extends TestCase
{
    public function test_getByType_returns_resource_when_present(): void
    {
        $iron = Resource::generateEmptyResource(ResourceType::IRON_ORE);
        $collection = new ResourceCollection([$iron]);

        self::assertSame($iron, $collection->getByType(ResourceType::IRON_ORE));
    }

    public function test_getByType_returns_null_when_missing(): void
    {
        $collection = new ResourceCollection();

        self::assertNull($collection->getByType(ResourceType::IRON_ORE));
    }

    public function test_getByTypeOrFail_throws_when_missing(): void
    {
        $collection = new ResourceCollection();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('iron_ore');

        $collection->getByTypeOrFail(ResourceType::IRON_ORE);
    }

    public function test_getByTypeOrFail_returns_resource_when_present(): void
    {
        $iron = Resource::generateEmptyResource(ResourceType::IRON_ORE);
        $collection = new ResourceCollection([$iron]);

        self::assertSame($iron, $collection->getByTypeOrFail(ResourceType::IRON_ORE));
    }
}
