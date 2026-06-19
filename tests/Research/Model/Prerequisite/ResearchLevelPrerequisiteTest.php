<?php

declare(strict_types=1);

namespace App\Tests\Research\Model\Prerequisite;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Research\Model\Prerequisite\PlayerResearchLookup;
use App\Research\Model\Prerequisite\ResearchLevelPrerequisite;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ResearchLevelPrerequisiteTest extends TestCase
{
    public function test_met_when_lookup_returns_high_level(): void
    {
        $prereq = new ResearchLevelPrerequisite('basic_mining', 2);
        $lookup = $this->lookup(['basic_mining' => 3]);

        self::assertTrue($prereq->isMetBy($this->player(), new DateTimeImmutable(), $lookup));
    }

    public function test_unmet_when_lookup_returns_lower_level(): void
    {
        $prereq = new ResearchLevelPrerequisite('basic_mining', 2);
        $lookup = $this->lookup(['basic_mining' => 1]);

        self::assertFalse($prereq->isMetBy($this->player(), new DateTimeImmutable(), $lookup));
    }

    public function test_describe(): void
    {
        $prereq = new ResearchLevelPrerequisite('metallurgy', 1);
        self::assertSame('Forschung metallurgy L1', $prereq->describe());
    }

    private function player(): Player
    {
        $p = new Player(PlayerId::generate());
        $p->claimPlanet(Planet::generatePlanet(PlanetId::generate()));

        return $p;
    }

    /**
     * @param array<string, int> $levels
     */
    private function lookup(array $levels): PlayerResearchLookup
    {
        return new class ($levels) implements PlayerResearchLookup {
            /** @param array<string, int> $levels */
            public function __construct(private array $levels)
            {
            }

            public function getPlayerResearchLevel(Player $p, string $s): int
            {
                return $this->levels[$s] ?? 0;
            }
        };
    }
}
