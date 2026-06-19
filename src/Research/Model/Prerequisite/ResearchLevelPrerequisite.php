<?php

declare(strict_types=1);

namespace App\Research\Model\Prerequisite;

use App\Player\Model\Player;
use DateTimeImmutable;

/**
 * T-170: Player muss eine andere Forschung auf >= $level haben.
 */
final readonly class ResearchLevelPrerequisite implements ResearchPrerequisite
{
    public function __construct(
        public string $slug,
        public int $level,
    ) {
    }

    public function isMetBy(Player $player, DateTimeImmutable $now, PlayerResearchLookup $lookup): bool
    {
        return $lookup->getPlayerResearchLevel($player, $this->slug) >= $this->level;
    }

    public function describe(): string
    {
        return sprintf('Forschung %s L%d', $this->slug, $this->level);
    }
}
