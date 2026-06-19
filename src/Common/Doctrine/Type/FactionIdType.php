<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Faction\ValueObject\FactionId;

final class FactionIdType extends AbstractUuidType
{
    public const NAME = 'faction_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return FactionId::class;
    }
}
