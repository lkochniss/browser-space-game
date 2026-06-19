<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class AlreadyResearchingException extends DomainException
{
    public function __construct(string $activeNodeSlug)
    {
        parent::__construct(sprintf('Player forscht bereits an "%s" — nur 1 Forschung gleichzeitig.', $activeNodeSlug));
    }
}
