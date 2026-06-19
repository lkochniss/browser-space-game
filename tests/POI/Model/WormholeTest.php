<?php

declare(strict_types=1);

namespace App\Tests\POI\Model;

use App\POI\Model\Wormhole;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use PHPUnit\Framework\TestCase;

final class WormholeTest extends TestCase
{
    public function test_unpaired_wormhole_has_null_twin(): void
    {
        $w = $this->makeWormhole();
        self::assertNull($w->getTwin());
    }

    public function test_pair_with_links_bidirectionally(): void
    {
        $a = $this->makeWormhole();
        $b = $this->makeWormhole();

        $a->pairWith($b);

        self::assertSame($b, $a->getTwin());
        self::assertSame($a, $b->getTwin());
    }

    public function test_pair_with_idempotent(): void
    {
        $a = $this->makeWormhole();
        $b = $this->makeWormhole();

        $a->pairWith($b);
        $a->pairWith($b);

        self::assertSame($b, $a->getTwin());
        self::assertSame($a, $b->getTwin());
    }

    public function test_required_tech_slug_default_null(): void
    {
        $w = $this->makeWormhole();
        self::assertNull($w->getRequiredTechSlug());
    }

    public function test_required_tech_slug_setter(): void
    {
        $w = $this->makeWormhole();
        $w->setRequiredTechSlug('ftl_tier_2');
        self::assertSame('ftl_tier_2', $w->getRequiredTechSlug());
    }

    private function makeWormhole(): Wormhole
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        return new Wormhole(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Test-Wormhole',
        );
    }
}
