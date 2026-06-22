<?php

declare(strict_types=1);

namespace App\Research\Exception;

use App\Planet\ValueObject\PlanetId;
use DomainException;

/**
 * T-025c: Multi-Lab-Opt-In Validation-Errors. Wirf für:
 *  - Primary-Lab-Planet gehört nicht Player
 *  - Primary-Lab nicht ready
 *  - Booster-Planet gehört nicht Player
 *  - Booster-Planet hat kein ready Lab
 *  - Primary-Planet auch in Booster-Liste
 *  - Duplicate-Booster-Planet
 */
final class InvalidLabSelectionException extends DomainException
{
    public static function primaryNotOwned(PlanetId $planetId): self
    {
        return new self(sprintf('Primary-Lab-Planet %s gehört nicht dem Player.', $planetId));
    }

    public static function primaryLabNotReady(PlanetId $planetId): self
    {
        return new self(sprintf('Primary-Lab-Planet %s hat kein fertiges RESEARCH_LAB.', $planetId));
    }

    public static function boosterNotOwned(PlanetId $planetId): self
    {
        return new self(sprintf('Booster-Lab-Planet %s gehört nicht dem Player.', $planetId));
    }

    public static function boosterLabNotReady(PlanetId $planetId): self
    {
        return new self(sprintf('Booster-Lab-Planet %s hat kein fertiges RESEARCH_LAB.', $planetId));
    }

    public static function primaryInBoosters(PlanetId $planetId): self
    {
        return new self(sprintf('Primary-Planet %s darf nicht gleichzeitig Booster sein.', $planetId));
    }

    public static function duplicateBooster(PlanetId $planetId): self
    {
        return new self(sprintf('Booster-Planet %s ist mehrfach angegeben.', $planetId));
    }
}
