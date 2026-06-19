<?php

declare(strict_types=1);

namespace App\Resource\Model;

use App\Resource\ValueObject\ResourceType;
use Doctrine\Common\Collections\ArrayCollection;
use OutOfBoundsException;

/**
 * @extends ArrayCollection<int, ResourceDeposit>
 */
class ResourceDepositCollection extends ArrayCollection
{
    public function getByType(ResourceType $type): ?ResourceDeposit
    {
        foreach ($this as $deposit) {
            if ($deposit->getResourceType() === $type) {
                return $deposit;
            }
        }

        return null;
    }

    public function getByTypeOrFail(ResourceType $type): ResourceDeposit
    {
        $deposit = $this->getByType($type);
        if ($deposit === null) {
            throw new OutOfBoundsException(
                sprintf('ResourceDeposit of type "%s" not present in collection', $type->value)
            );
        }

        return $deposit;
    }
}
