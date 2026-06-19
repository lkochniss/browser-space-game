<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class PrerequisiteNotMetException extends DomainException
{
    public function __construct(string $nodeSlug, string $missingPrereqSlug, int $requiredLevel, int $actualLevel)
    {
        parent::__construct(sprintf(
            'Prerequisite für "%s" nicht erfüllt: braucht "%s" Level %d, hat %d.',
            $nodeSlug,
            $missingPrereqSlug,
            $requiredLevel,
            $actualLevel,
        ));
    }
}
