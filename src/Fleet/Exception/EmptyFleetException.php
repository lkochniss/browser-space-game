<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use DomainException;

final class EmptyFleetException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot create fleet with empty ship list');
    }
}
