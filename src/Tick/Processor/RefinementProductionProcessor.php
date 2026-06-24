<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Planet\Model\Planet;
use App\Resource\Service\RefinementConfig;
use App\Resource\ValueObject\RefinementRecipe;
use App\Resource\ValueObject\ResourceCategory;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

/**
 * T-003: Refinement-Tick — verarbeitet alle Refineries auf dem Planet.
 *
 * T-067 Q3 Snapshot-Single-Step-pro-Tick:
 * Refined-Outputs werden pre-Tick gesnapshottet, damit Cascade (Tier-1-Output
 * → Tier-2-Input im selben Tick) verhindert wird. Iron-Ore → Iron-Bar →
 * Steel → Hull-Plate läuft also progressiv über mehrere Ticks.
 *
 * FINITE-Inputs (Erze, Coal, etc.) werden live abgefragt — kein Snapshot —
 * weil Mining-Output dieses Ticks bereits VOR Refinement im
 * `ResourceProductionProcessor` produziert wurde und damit verfügbar sein soll.
 */
readonly class RefinementProductionProcessor implements TickProcessorInterface
{
    public function __construct(private RefinementConfig $config)
    {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        // T-067: Snapshot der REFINED-Amounts vor dem Tick. Verhindert Cascade
        // (Tier-1-Output kann nicht im selben Tick zu Tier-2-Input werden).
        $refinedSnapshot = $this->snapshotRefined($planet);

        // T-065 Power-Throttle: bei Unter-Versorgung drosselt Refinement proportional.
        $powerThrottle = $planet->getPowerThrottleRatio($now);
        if ($powerThrottle <= 0.0) {
            return;
        }

        foreach ($planet->getBuildings() as $building) {
            $recipe = $this->config->getRecipeForBuilding($building->getType());
            if ($recipe === null) {
                continue;
            }

            // T-062: Building wirkt nur wenn ready
            if (!$building->isReady($now)) {
                continue;
            }

            // T-065: Power-Throttle wirkt vor Input-/Storage-Cap-Clamp.
            $desiredOutput = (int) floor($recipe->outputAmount * $building->getLevel() * $powerThrottle);
            if ($desiredOutput <= 0) {
                continue;
            }

            $maxByInputs = $this->maxOutputByAvailableInputs($planet, $recipe, $refinedSnapshot);

            // Storage-cap stop (T-061): refinement pauses when output storage full.
            $cap = $planet->getStorageCapacity($recipe->output);
            $currentOutput = $this->getAmount($planet, $recipe->output);
            $capRoom = max(0, $cap - $currentOutput);

            $actualOutput = min($desiredOutput, $maxByInputs, $capRoom);

            if ($actualOutput <= 0) {
                continue;
            }

            $this->debitInputs($planet, $recipe, $actualOutput, $refinedSnapshot);
            $this->creditOutput($planet, $recipe->output, $actualOutput);
        }
    }

    /**
     * @return array<string, int> Map<ResourceType.value, snapshot-amount> für REFINED-Resources
     */
    private function snapshotRefined(Planet $planet): array
    {
        $snapshot = [];
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType()->getCategory() === ResourceCategory::REFINED) {
                $snapshot[$resource->getType()->value] = $resource->getAmount();
            }
        }

        return $snapshot;
    }

    /**
     * @param array<string, int> $refinedSnapshot
     */
    private function maxOutputByAvailableInputs(
        Planet $planet,
        RefinementRecipe $recipe,
        array $refinedSnapshot,
    ): int {
        $max = PHP_INT_MAX;
        foreach ($recipe->iterateInputs() as [$inputType, $perUnit]) {
            $available = $this->effectiveAvailableInput($planet, $inputType, $refinedSnapshot);
            $possible = intdiv($available, $perUnit);
            $max = min($max, $possible);
        }

        return $max === PHP_INT_MAX ? 0 : $max;
    }

    /**
     * Für REFINED-Inputs nutzen wir das Snapshot (verhindert Cascade); zusätzlich
     * cappen wir mit dem Live-Wert (verhindert Over-Debit, falls mehrere
     * Refineries denselben REFINED-Input konsumieren würden — heute nicht der
     * Fall, aber Future-Safe). FINITE/RENEWABLE-Inputs gehen direkt auf Live.
     *
     * @param array<string, int> $refinedSnapshot
     */
    private function effectiveAvailableInput(
        Planet $planet,
        ResourceType $inputType,
        array $refinedSnapshot,
    ): int {
        $live = $this->getAmount($planet, $inputType);
        if ($inputType->getCategory() === ResourceCategory::REFINED) {
            $snapshot = $refinedSnapshot[$inputType->value] ?? 0;

            return min($snapshot, $live);
        }

        return $live;
    }

    /**
     * @param array<string, int> $refinedSnapshot
     */
    private function debitInputs(
        Planet $planet,
        RefinementRecipe $recipe,
        int $outputUnits,
        array &$refinedSnapshot,
    ): void {
        foreach ($recipe->iterateInputs() as [$inputType, $perUnit]) {
            $resource = $planet->getResource($inputType);
            $resource->setAmount($resource->getAmount() - $perUnit * $outputUnits);

            // Snapshot mitsenken für REFINED-Inputs, damit Future-Refineries (im
            // selben Tick, falls Recipes sich mal shared-input teilen) die schon
            // verbrauchte Menge sehen.
            if ($inputType->getCategory() === ResourceCategory::REFINED) {
                $refinedSnapshot[$inputType->value] = max(
                    0,
                    ($refinedSnapshot[$inputType->value] ?? 0) - $perUnit * $outputUnits,
                );
            }
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
