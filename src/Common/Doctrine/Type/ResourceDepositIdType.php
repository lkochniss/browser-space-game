<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Resource\ValueObject\ResourceDepositId;

final class ResourceDepositIdType extends AbstractUuidType
{
    public const NAME = 'resource_deposit_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return ResourceDepositId::class;
    }
}
