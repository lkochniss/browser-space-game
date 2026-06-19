<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Planet\ValueObject\PlanetId;

final class PlanetIdType extends AbstractUuidType
{
    public const NAME = 'planet_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return PlanetId::class;
    }
}
