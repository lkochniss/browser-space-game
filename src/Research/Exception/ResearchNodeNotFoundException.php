<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class ResearchNodeNotFoundException extends DomainException
{
    public function __construct(string $slug)
    {
        parent::__construct(sprintf('Research-Node "%s" nicht in ResearchTree registriert', $slug));
    }
}
