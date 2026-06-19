<?php

declare(strict_types=1);

namespace App\Tests\Faction\Model;

use App\Faction\Model\Faction;
use App\Faction\ValueObject\FactionId;
use App\Faction\ValueObject\FactionType;
use PHPUnit\Framework\TestCase;

final class FactionTest extends TestCase
{
    public function test_hostile_faction_flag(): void
    {
        $faction = new Faction(
            id: FactionId::generate(),
            slug: 'pirate_consortium',
            name: 'Pirat-Konsortium',
            type: FactionType::PIRATE,
            isAlwaysHostile: true,
            defaultReputation: -100,
            description: 'Test',
        );

        self::assertTrue($faction->isAlwaysHostile());
        self::assertSame(-100, $faction->getDefaultReputation());
        self::assertSame(FactionType::PIRATE, $faction->getType());
    }

    public function test_neutral_faction_flag(): void
    {
        $faction = new Faction(
            id: FactionId::generate(),
            slug: 'merchant_guild',
            name: 'Galaktische Händler-Gilde',
            type: FactionType::MERCHANT_GUILD,
            isAlwaysHostile: false,
            defaultReputation: 0,
            description: 'Test',
        );

        self::assertFalse($faction->isAlwaysHostile());
        self::assertSame(0, $faction->getDefaultReputation());
        self::assertSame(FactionType::MERCHANT_GUILD, $faction->getType());
    }
}
