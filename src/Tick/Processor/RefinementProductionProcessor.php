<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Planet\Model\Planet;
use App\Resource\Service\RefinementConfig;
use App\Resource\ValueObject\RefinementRecipe;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

readonly class RefinementProductionProcessor implements TickProcessorInterface
{
    public function __construct(private RefinementConfig $config)
    {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        foreach ($planet->getBuildings() as $building) {
            $recipe = $this->config->getRecipeForBuilding($building->getType());
            if ($recipe === null) {
                continue;
            }

            // T-062: Building wirkt nur wenn ready
            if (!$building->isReady($now)) {
                continue;
            }

            $desiredOutput = $recipe->outputAmount * $building->getLevel();
            if ($desiredOutput <= 0) {
                continue;
            }

            $maxByInputs = $this->maxOutputByAvailableInputs($planet, $recipe);

            // Storage-cap stop (T-061): refinement pauses when output storage full.
            $cap = $planet->getStorageCapacity($recipe->output);
            $currentOutput = $this->getAmount($planet, $recipe->output);
            $capRoom = max(0, $cap - $currentOutput);

            $actualOutput = min($desiredOutput, $maxByInputs, $capRoom);

            if ($actualOutput <= 0) {
                continue;
            }

            $this->debitInputs($planet, $recipe, $actualOutput);
            $this->creditOutput($planet, $recipe->output, $actualOutput);
        }
    }

    private function maxOutputByAvailableInputs(Planet $planet, RefinementRecipe $recipe): int
    {
        $max = PHP_INT_MAX;
        foreach ($recipe->iterateInputs() as [$inputType, $perUnit]) {
            $available = $this->getAmount($planet, $inputType);
            $possible = intdiv($available, $perUnit);
            $max = min($max, $possible);
        }

        return $max;
    }

    private function debitInputs(Planet $planet, RefinementRecipe $recipe, int $outputUnits): void
    {
        foreach ($recipe->iterateInputs() as [$inputType, $perUnit]) {
            $resource = $planet->getResource($inputType);
            $resource->setAmount($resource->getAmount() - $perUnit * $outputUnits);
        }
    }

    private function creditOutput(Planet $planet, ResourceType $output, int $units): void
    {
        $resource = $planet->ensureResource($output);
        $resource->setAmount($resource->getAmount() + $units);
    }

    private function getAmount(Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource->getAmount();
            }
        }

        return 0;
    }
}
