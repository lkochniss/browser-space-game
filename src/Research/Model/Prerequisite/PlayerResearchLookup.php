<?php

declare(strict_types=1);

namespace App\Research\Model\Prerequisite;

use App\Player\Model\Player;

/**
 * T-170 Lookup-Closure für ResearchPrerequisite::isMetBy. Vermeidet, dass
 * jede Prerequisite-Implementation Repository-Dependencies braucht — der
 * StartResearchCommandService injectet einen Lookup, der gegen
 * PlayerResearchRepository auflöst.
 */
interface PlayerResearchLookup
{
    public function getPlayerResearchLevel(Player $player, string $nodeSlug): int;
}
