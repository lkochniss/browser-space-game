<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Probe\ValueObject\ProbeId;

final class ProbeIdType extends AbstractUuidType
{
    public const NAME = 'probe_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return ProbeId::class;
    }
}
