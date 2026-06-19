<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Resource\ValueObject\ResourceId;

final class ResourceIdType extends AbstractUuidType
{
    public const NAME = 'resource_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return ResourceId::class;
    }
}
