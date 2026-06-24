<?php

declare(strict_types=1);

namespace App\Trade\Exception;

final class InvalidTradeRouteException extends \DomainException
{
    public static function planetNotOwnedByPlayer(string $planetId): self
    {
        return new self(sprintf('Planet %s gehört nicht dem Player.', $planetId));
    }

    public static function sameSourceAndTarget(): self
    {
        return new self('Source- und Target-Planet müssen verschieden sein.');
    }

    public static function shipCargoTooSmall(int $needed, int $available): self
    {
        return new self(sprintf(
            'Ship-Cargo-Capacity %d reicht nicht für Outbound-Qty %d.',
            $available,
            $needed,
        ));
    }

    public static function shipNotDocked(): self
    {
        return new self('Ship muss am Source-Planet docked sein um eine Route zu starten.');
    }
}
