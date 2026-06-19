<?php

declare(strict_types=1);

namespace App\Tests\Faction\Service;

use App\Faction\Repository\FactionRepository;
use App\Faction\Service\FactionSeedService;
use App\Tests\Integration\IntegrationTestCase;

final class FactionSeedServiceTest extends IntegrationTestCase
{
    public function test_seed_creates_four_default_factions(): void
    {
        $repo = self::getContainer()->get(FactionRepository::class);
        $factions = $repo->findAll();

        self::assertCount(4, $factions);

        $slugs = array_map(static fn ($f) => $f->getSlug(), $factions);
        sort($slugs);
        self::assertSame(
            ['merchant_guild', 'pirate_consortium', 'renegade_warbands', 'xenos_splinter'],
            $slugs,
        );
    }

    public function test_seed_is_idempotent(): void
    {
        // IntegrationTestCase setUp already seeded once. Second call must not duplicate.
        self::getContainer()->get(FactionSeedService::class)->seed();

        $repo = self::getContainer()->get(FactionRepository::class);
        self::assertCount(4, $repo->findAll());
    }
}
