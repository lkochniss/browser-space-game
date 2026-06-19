<?php

declare(strict_types=1);

namespace App\Building\Exception;

use DomainException;

class InsufficientPopulationException extends DomainException
{
    public function __construct(
        public readonly int $required,
        public readonly int $availableFree,
    ) {
        parent::__construct(sprintf(
            'Insufficient free population: need %d, have %d',
            $required,
            $availableFree,
        ));
    }
}
