<?php

namespace App\Resource\Service;

use App\Resource\ValueObject\ResourceType;

class ResourceProductionConfig
{
    private array $baseProduction = [
        ResourceType::IRON_ORE->value => 10.0,
    ];

    public function getBaseProduction(ResourceType $resource): float
    {
        return $this->baseProduction[$resource->value] ?? 0.0;
    }
}
