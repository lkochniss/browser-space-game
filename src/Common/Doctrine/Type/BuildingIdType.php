<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Building\ValueObject\BuildingId;

final class BuildingIdType extends AbstractUuidType
{
    public const NAME = 'building_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return BuildingId::class;
    }
}
