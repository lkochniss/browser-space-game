<?php

declare(strict_types=1);

namespace App\Building\ValueObject;

use App\Resource\ValueObject\ResourceType;

readonly class BuildingCost
{
    /**
     * @param array<string, int> $resources Map ResourceType.value → required amount
     */
    public function __construct(
        public array $resources,
        public int $populationCost,
    ) {
    }

    /**
     * @return iterable<array{ResourceType, int}>
     */
    public function iterateResources(): iterable
    {
        foreach ($this->resources as $resourceTypeValue => $amount) {
            yield [ResourceType::from($resourceTypeValue), $amount];
        }
    }
}
