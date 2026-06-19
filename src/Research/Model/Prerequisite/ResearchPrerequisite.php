<?php

declare(strict_types=1);

namespace App\Research\Model\Prerequisite;

use App\Player\Model\Player;
use DateTimeImmutable;

/**
 * T-170 Polymorpher Research-Prerequisite. Implementations:
 *  - ResearchLevelPrerequisite (player hat Forschung X auf Level Y)
 *  - BuildingLevelPrerequisite (player hat Building X auf Level Y, ready)
 */
interface ResearchPrerequisite
{
    /**
     * Liefert true wenn der Player den Prereq aktuell erfüllt.
     */
    public function isMetBy(Player $player, DateTimeImmutable $now, PlayerResearchLookup $lookup): bool;

    /**
     * Human-Reader: "Forschung basic_mining L1" / "Building IRON_MINE L2".
     */
    public function describe(): string;
}
