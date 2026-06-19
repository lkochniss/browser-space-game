<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\SolarSystem\ValueObject\SolarSystemId;

final class SolarSystemIdType extends AbstractUuidType
{
    public const NAME = 'solar_system_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return SolarSystemId::class;
    }
}
