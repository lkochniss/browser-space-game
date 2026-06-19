<?php

declare(strict_types=1);

namespace App\Tests\Resource\Model;

use App\Resource\Model\ResourceDeposit;
use App\Resource\Model\ResourceDepositCollection;
use App\Resource\ValueObject\ResourceType;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ResourceDepositCollectionTest extends TestCase
{
    public function test_getByType_returns_deposit_when_present(): void
    {
        $deposit = ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 100);
        $collection = new ResourceDepositCollection([$deposit]);

        self::assertSame($deposit, $collection->getByType(ResourceType::IRON_ORE));
    }

    public function test_getByType_returns_null_when_missing(): void
    {
        $collection = new ResourceDepositCollection();

        self::assertNull($collection->getByType(ResourceType::IRON_ORE));
    }

    public function test_getByTypeOrFail_throws_when_missing(): void
    {
        $collection = new ResourceDepositCollection();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('iron_ore');

        $collection->getByTypeOrFail(ResourceType::IRON_ORE);
    }
}
