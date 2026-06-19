<?php

declare(strict_types=1);

namespace App\Research\Model\Prerequisite;

use App\Building\ValueObject\BuildingType;
use App\Player\Model\Player;
use DateTimeImmutable;

/**
 * T-170: Player muss Building X auf >= $level besitzen UND `isReady($now)`.
 *
 * "Currently-has-ready"-Semantik (T-170 Decision):
 *  - Während Bau-Phase: nicht erfüllt (finishedAt > now)
 *  - Während Upgrade-Phase: nicht erfüllt — Player muss warten bis Upgrade fertig
 *  - Building zerstört (Foundation hat keinen Demolish, also irrelevant): nicht erfüllt
 */
final readonly class BuildingLevelPrerequisite implements ResearchPrerequisite
{
    public function __construct(
        public BuildingType $buildingType,
        public int $level,
    ) {
    }

    public function isMetBy(Player $player, DateTimeImmutable $now, PlayerResearchLookup $lookup): bool
    {
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getBuildings() as $b) {
                if ($b->getType() !== $this->buildingType) {
                    continue;
                }
                if (!$b->isReady($now)) {
                    continue;
                }
                if ($b->getLevel() >= $this->level) {
                    return true;
                }
            }
        }

        return false;
    }

    public function describe(): string
    {
        return sprintf('Building %s L%d', $this->buildingType->value, $this->level);
    }
}
