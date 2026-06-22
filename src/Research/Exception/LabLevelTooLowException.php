<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

/**
 * T-069: Effective Lab-Level (Primary + Booster-Beitrag, T-025c) reicht nicht
 * für die erforderte Tier-Stufe des Nodes.
 */
final class LabLevelTooLowException extends DomainException
{
    public function __construct(
        public readonly string $nodeSlug,
        public readonly int $requiredLabLevel,
        public readonly float $effectiveLabLevel,
    ) {
        parent::__construct(sprintf(
            'Node "%s" benötigt Lab-Level >= %d (effective: %.2f) — Lab upgraden oder Booster hinzufügen.',
            $nodeSlug,
            $requiredLabLevel,
            $effectiveLabLevel,
        ));
    }
}
