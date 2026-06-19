<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\POI\ValueObject\PoiId;
use DomainException;

final class InvalidSalvageTargetException extends DomainException
{
    public function __construct(
        public readonly PoiId $poiId,
        public readonly string $reason,
    ) {
        parent::__construct(sprintf(
            'POI "%s" is not a valid salvage target: %s',
            $poiId,
            $reason,
        ));
    }
}
