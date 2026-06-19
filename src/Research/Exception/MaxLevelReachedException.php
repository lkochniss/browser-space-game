<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class MaxLevelReachedException extends DomainException
{
    public function __construct(string $nodeSlug, int $maxLevel)
    {
        parent::__construct(sprintf('Research-Node "%s" ist bereits auf Max-Level %d.', $nodeSlug, $maxLevel));
    }
}
