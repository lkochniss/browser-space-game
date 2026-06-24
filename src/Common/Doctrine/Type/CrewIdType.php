<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Crew\ValueObject\CrewId;

final class CrewIdType extends AbstractUuidType
{
    public const NAME = 'crew_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return CrewId::class;
    }
}
