<?php

declare(strict_types=1);

namespace App\Tests\Resource\ValueObject;

use App\Resource\ValueObject\ResourceCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceCategoryTest extends TestCase
{
    /**
     * @return array<string, array{ResourceCategory, int}>
     */
    public static function baseCapProvider(): array
    {
        return [
            'finite'    => [ResourceCategory::FINITE,    100],
            'renewable' => [ResourceCategory::RENEWABLE, 500],
            'refined'   => [ResourceCategory::REFINED,   100],
        ];
    }

    #[DataProvider('baseCapProvider')]
    public function test_base_cap(ResourceCategory $category, int $expected): void
    {
        self::assertSame($expected, $category->getBaseCap());
    }
}
