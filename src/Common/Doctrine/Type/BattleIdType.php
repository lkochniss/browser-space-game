<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Battle\ValueObject\BattleId;

final class BattleIdType extends AbstractUuidType
{
    public const NAME = 'battle_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return BattleId::class;
    }
}
