<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\PropulsionType;
use DomainException;

/**
 * T-026c: Player versucht ein Schiff mit einem Antrieb zu bauen, für den ihm
 * die Forschung fehlt. Z.B. ION-Antrieb erfordert `propulsion_ion` Lvl 1+.
 */
final class PropulsionResearchNotMetException extends DomainException
{
    public function __construct(
        public readonly PropulsionType $propulsion,
        public readonly string $requiredSlug,
    ) {
        parent::__construct(sprintf(
            'Schiff mit Antrieb "%s" erfordert die Forschung "%s" — bitte vorher erforschen.',
            $propulsion->value,
            $requiredSlug,
        ));
    }
}
