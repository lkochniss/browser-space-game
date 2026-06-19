<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Ship\ValueObject\ShipId;

final class ShipIdType extends AbstractUuidType
{
    public const NAME = 'ship_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return ShipId::class;
    }
}
