<?php

declare(strict_types=1);

namespace App\Resource\ValueObject;

use App\Building\ValueObject\BuildingType;

readonly class RefinementRecipe
{
    /**
     * @param array<string, int> $inputs Map ResourceType.value → amount required per outputAmount
     */
    public function __construct(
        public ResourceType $output,
        public int $outputAmount,
        public array $inputs,
        public BuildingType $building,
    ) {
    }

    /**
     * @return iterable<array{ResourceType, int}>
     */
    public function iterateInputs(): iterable
    {
        foreach ($this->inputs as $resourceTypeValue => $amount) {
            yield [ResourceType::from($resourceTypeValue), $amount];
        }
    }
}
