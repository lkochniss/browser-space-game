<?php

declare(strict_types=1);

namespace App\Tests\Player\ValueObject;

use App\Player\ValueObject\PlayerBackground;
use PHPUnit\Framework\TestCase;

/**
 * T-122: PlayerBackground-Enum mit Display-Names + Descriptions.
 */
final class PlayerBackgroundTest extends TestCase
{
    public function test_has_five_cases(): void
    {
        self::assertCount(5, PlayerBackground::cases());
    }

    public function test_each_case_has_display_name_and_description(): void
    {
        foreach (PlayerBackground::cases() as $bg) {
            self::assertNotEmpty($bg->getDisplayName(), $bg->value);
            self::assertNotEmpty($bg->getDescription(), $bg->value);
        }
    }

    public function test_display_names_are_unique(): void
    {
        $names = array_map(static fn (PlayerBackground $b): string => $b->getDisplayName(), PlayerBackground::cases());
        self::assertSame($names, array_unique($names));
    }
}
