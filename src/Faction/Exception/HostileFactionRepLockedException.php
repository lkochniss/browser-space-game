<?php

declare(strict_types=1);

namespace App\Faction\Exception;

use App\Faction\Model\Faction;
use DomainException;

class HostileFactionRepLockedException extends DomainException
{
    public function __construct(public readonly Faction $faction)
    {
        parent::__construct(sprintf(
            'Faction "%s" is always hostile — reputation cannot be changed',
            $faction->getSlug(),
        ));
    }
}
