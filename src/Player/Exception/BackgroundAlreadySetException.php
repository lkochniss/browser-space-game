<?php

declare(strict_types=1);

namespace App\Player\Exception;

use App\Player\ValueObject\PlayerBackground;
use DomainException;

/**
 * T-122: Background ist permanent. Wenn Player schon einen Background hat,
 * wirft ein erneuter `setBackground()`-Aufruf diese Exception.
 */
final class BackgroundAlreadySetException extends DomainException
{
    public function __construct(public readonly PlayerBackground $current)
    {
        parent::__construct(sprintf(
            'Player-Background ist bereits gesetzt ("%s") und permanent — kein Re-Spec möglich.',
            $current->value,
        ));
    }
}
