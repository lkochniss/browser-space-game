<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Player\ValueObject\PlayerId;

final class PlayerIdType extends AbstractUuidType
{
    public const NAME = 'player_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return PlayerId::class;
    }
}
