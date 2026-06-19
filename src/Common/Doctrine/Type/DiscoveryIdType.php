<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Discovery\ValueObject\DiscoveryId;

class DiscoveryIdType extends AbstractUuidType
{
    public const NAME = 'discovery_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return DiscoveryId::class;
    }
}
