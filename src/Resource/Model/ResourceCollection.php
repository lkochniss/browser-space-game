<?php

declare(strict_types=1);

namespace App\Resource\Model;

use App\Resource\ValueObject\ResourceType;
use Doctrine\Common\Collections\ArrayCollection;
use OutOfBoundsException;

/**
 * @extends ArrayCollection<int, Resource>
 */
class ResourceCollection extends ArrayCollection
{
    public function getByType(ResourceType $type): ?Resource
    {
        foreach ($this as $resource) {
            if ($resource->getType() === $type) {
                return $resource;
            }
        }

        return null;
    }

    public function getByTypeOrFail(ResourceType $type): Resource
    {
        $resource = $this->getByType($type);
        if ($resource === null) {
            throw new OutOfBoundsException(
                sprintf('Resource of type "%s" not present in collection', $type->value)
            );
        }

        return $resource;
    }
}
