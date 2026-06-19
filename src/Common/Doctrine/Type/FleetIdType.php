<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Fleet\ValueObject\FleetId;

final class FleetIdType extends AbstractUuidType
{
    public const NAME = 'fleet_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return FleetId::class;
    }
}
