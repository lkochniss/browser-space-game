<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Building\Service\RecyclingTable;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\Randomizer;
use App\Planet\Model\Planet;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

/**
 * T-021 RecyclingProcessor: konsumiert pro Tick `level × UNITS_PER_LEVEL` DEBRIS-Items
 * vom Planet (priorität LOW → MEDIUM → HIGH) und würfelt für jedes konsumierte Item
 * ein Resource-Output via RecyclingTable.
 *
 * Stop-Conditions:
 *  - Keine Recycling-Plant (ready) auf Planet → no-op
 *  - Keine Debris-Items auf Planet → no-op
 *
 * Zufall ist via Randomizer-Service injizierbar (Tests nutzen Stub).
 */
readonly class RecyclingProcessor implements TickProcessorInterface
{
    public const UNITS_PER_LEVEL = 2;

    public function __construct(
        private RecyclingTable $table,
        private Randomizer $randomizer,
    ) {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        $totalLevel = 0;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getType() !== BuildingType::RECYCLING_PLANT) {
                continue;
            }
            if (!$b->isReady($now)) {
                continue;
            }
            $totalLevel += $b->getLevel();
        }
        if ($totalLevel === 0) {
            return;
        }

        $unitsBudget = $totalLevel * self::UNITS_PER_LEVEL;

        // Konsumiere Debris-Items in Reihenfolge LOW → MEDIUM → HIGH
        foreach ([ResourceType::DEBRIS_LOW, ResourceType::DEBRIS_MEDIUM, ResourceType::DEBRIS_HIGH] as $debrisType) {
            if ($unitsBudget <= 0) {
                break;
            }
            try {
                $debrisRes = $planet->getResource($debrisType);
            } catch (\Throwable) {
                continue;
            }
            $available = $debrisRes->getAmount();
            if ($available <= 0) {
                continue;
            }

            $consume = min($unitsBudget, $available);
            $debrisRes->setAmount($available - $consume);
            $unitsBudget -= $consume;

            for ($i = 0; $i < $consume; $i++) {
                $output = $this->rollOutput($debrisType);
                if ($output === null) {
                    continue;
                }
                [$resourceType, $amount] = $output;
                $planet->ensureResource($resourceType)->setAmount(
                    $planet->ensureResource($resourceType)->getAmount() + $amount,
                );
            }
        }
    }

    /**
     * @return array{0: ResourceType, 1: int}|null
     */
    private function rollOutput(ResourceType $debrisType): ?array
    {
        $entries = $this->table->entries($debrisType);
        if ($entries === []) {
            return null;
        }
        $totalWeight = 0;
        foreach ($entries as $e) {
            $totalWeight += $e['weight'];
        }
        if ($totalWeight <= 0) {
            return null;
        }
        $roll = $this->randomizer->intBetween(1, $totalWeight);
        $cumulative = 0;
        foreach ($entries as $e) {
            $cumulative += $e['weight'];
            if ($roll <= $cumulative) {
                if ($e['output'] === null) {
                    return null;
                }
                $amount = $this->randomizer->intBetween($e['minAmount'], $e['maxAmount']);

                return [$e['output'], $amount];
            }
        }

        return null;
    }
}
