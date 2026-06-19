<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Planet\Model\Planet;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

/**
 * T-062: Re-evaluates Population-Cap each tick using the current game-clock.
 *
 * Effekt: Sobald ein in-progress Hub durch Wall-Clock fertig wird, fließt sein Cap-Bonus
 * im nächsten Tick automatisch in `population.cap` ein. Idempotent — ein Recalc pro Tick
 * pro Planet, ohne State-Tracking.
 *
 * Reihenfolge in TickEngine: ZUERST (vor Production/Refinement/Consumption), damit
 * neu fertige Buildings im selben Tick wirken.
 */
readonly class ConstructionCompletionProcessor implements TickProcessorInterface
{
    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        $planet->recalculatePopulationCap($now);
    }
}
