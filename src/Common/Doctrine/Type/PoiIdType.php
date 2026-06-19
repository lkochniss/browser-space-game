<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\POI\ValueObject\PoiId;

final class PoiIdType extends AbstractUuidType
{
    public const NAME = 'poi_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return PoiId::class;
    }
}
