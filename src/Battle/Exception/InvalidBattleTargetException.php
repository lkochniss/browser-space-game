<?php

declare(strict_types=1);

namespace App\Battle\Exception;

final class InvalidBattleTargetException extends \DomainException
{
    public static function bothNull(): self
    {
        return new self('StartBattleCommand needs either defenderFleetId OR defenderPlanetId');
    }

    public static function bothSet(): self
    {
        return new self('StartBattleCommand cannot have BOTH defenderFleetId AND defenderPlanetId — choose one');
    }

    public static function notInSameSystem(): self
    {
        return new self('Attacker- und Defender-Fleet müssen im selben SolarSystem sein.');
    }

    public static function emptyFleet(string $side): self
    {
        return new self(sprintf('%s-Fleet ist leer (keine Ships).', $side));
    }
}
