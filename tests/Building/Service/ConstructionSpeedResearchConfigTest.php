<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Service\ConstructionSpeedResearchConfig;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\PlayerResearch;
use App\Research\Repository\PlayerResearchRepository;
use PHPUnit\Framework\TestCase;

final class ConstructionSpeedResearchConfigTest extends TestCase
{
    public function test_no_player_returns_one(): void
    {
        $config = $this->makeConfig([]);
        self::assertSame(1.0, $config->getMultiplier(null));
    }

    public function test_no_research_returns_one(): void
    {
        $config = $this->makeConfig([]);
        self::assertSame(1.0, $config->getMultiplier(new Player(PlayerId::generate())));
    }

    public function test_l1_gives_1_10_multiplier(): void
    {
        $config = $this->makeConfig(['construction_speed_1' => 1]);
        self::assertEqualsWithDelta(1.10, $config->getMultiplier(new Player(PlayerId::generate())), 0.001);
    }

    public function test_l3_gives_1_331_multiplier(): void
    {
        $config = $this->makeConfig(['construction_speed_1' => 3]);
        self::assertEqualsWithDelta(1.331, $config->getMultiplier(new Player(PlayerId::generate())), 0.001);
    }

    /**
     * @param array<string, int> $researchLevels
     */
    private function makeConfig(array $researchLevels): ConstructionSpeedResearchConfig
    {
        $repo = $this->createMock(PlayerResearchRepository::class);
        $repo->method('findOneByPlayerAndSlug')
            ->willReturnCallback(function ($player, $slug) use ($researchLevels) {
                if (!isset($researchLevels[$slug])) {
                    return null;
                }
                $entry = $this->createMock(PlayerResearch::class);
                $entry->method('getLevel')->willReturn($researchLevels[$slug]);

                return $entry;
            });

        return new ConstructionSpeedResearchConfig($repo);
    }
}
