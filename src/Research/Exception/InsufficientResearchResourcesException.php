<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class InsufficientResearchResourcesException extends DomainException
{
    public function __construct(string $resourceVal, int $needed, int $available)
    {
        parent::__construct(sprintf(
            'Resource %s reicht nicht für Forschung: %d benötigt, %d verfügbar (verteilt auf alle Player-Planeten).',
            $resourceVal,
            $needed,
            $available,
        ));
    }
}
