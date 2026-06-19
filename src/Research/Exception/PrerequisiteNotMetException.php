<?php

declare(strict_types=1);

namespace App\Research\Exception;

use App\Research\Model\Prerequisite\ResearchPrerequisite;
use DomainException;

class PrerequisiteNotMetException extends DomainException
{
    public function __construct(string $nodeSlug, ResearchPrerequisite $missing)
    {
        parent::__construct(sprintf(
            'Prerequisite für "%s" nicht erfüllt: %s.',
            $nodeSlug,
            $missing->describe(),
        ));
    }
}
