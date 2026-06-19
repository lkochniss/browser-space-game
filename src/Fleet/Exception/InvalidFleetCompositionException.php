<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use DomainException;

final class InvalidFleetCompositionException extends DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct(sprintf('Invalid fleet composition: %s', $reason));
    }
}
