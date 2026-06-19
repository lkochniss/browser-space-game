<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\POI\ValueObject\PoiId;
use DomainException;

final class PoiNotFoundException extends DomainException
{
    public function __construct(public readonly PoiId $poiId)
    {
        parent::__construct(sprintf('POI "%s" not found', $poiId));
    }
}
