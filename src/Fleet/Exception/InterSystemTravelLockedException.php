<?php

declare(strict_types=1);

namespace App\Fleet\Exception;

use DomainException;

/**
 * T-026: Inter-System-Travel ist hinter FTL-Forschung versteckt.
 * Foundation: Player braucht `ftl_hyperdrive` Level 1.
 */
class InterSystemTravelLockedException extends DomainException
{
    public function __construct(string $requiredSlug = 'ftl_hyperdrive', int $requiredLevel = 1)
    {
        parent::__construct(sprintf(
            'Inter-System-Reise gesperrt — erfordert Forschung "%s" auf Level %d.',
            $requiredSlug,
            $requiredLevel,
        ));
    }
}
