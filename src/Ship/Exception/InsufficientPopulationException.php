<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use DomainException;

final class InsufficientPopulationException extends DomainException
{
    public function __construct(
        public readonly int $required,
        public readonly int $free,
    ) {
        parent::__construct(sprintf(
            'Insufficient free population for ship build: need %d, have %d',
            $required,
            $free,
        ));
    }
}
