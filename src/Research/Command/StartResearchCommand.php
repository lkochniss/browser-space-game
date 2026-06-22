<?php

declare(strict_types=1);

namespace App\Research\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;

readonly class StartResearchCommand implements CommandInterface
{
    /**
     * T-025c: Multi-Lab Opt-In.
     *
     * @param list<PlanetId> $boosterLabPlanetIds zusätzliche Lab-Planeten als Booster (default `[]` = Single-Lab)
     */
    public function __construct(
        public PlayerId $playerId,
        public string $nodeSlug,
        public ?PlanetId $primaryLabPlanetId = null,
        public array $boosterLabPlanetIds = [],
    ) {
    }
}
