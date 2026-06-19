<?php

declare(strict_types=1);

namespace App\POI\Exception;

use App\Player\ValueObject\PlayerId;
use DomainException;

final class PlayerNotFoundException extends DomainException
{
    public function __construct(public readonly PlayerId $playerId)
    {
        parent::__construct(sprintf('Player "%s" not found', $playerId));
    }
}
