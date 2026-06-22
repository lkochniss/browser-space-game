<?php

declare(strict_types=1);

namespace App\Player\Exception;

use App\Player\ValueObject\PlayerId;
use DomainException;

final class PlayerNotFoundException extends DomainException
{
    public function __construct(public readonly PlayerId $playerId)
    {
        parent::__construct(sprintf('Player "%s" nicht gefunden.', $playerId));
    }
}
