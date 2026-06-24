<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use DomainException;

/**
 * T-104a: Player will Crew trainieren, hat aber keine fertige ACADEMY.
 */
final class MissingAcademyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Crew-Training erfordert eine fertige ACADEMY beim Player.');
    }
}
