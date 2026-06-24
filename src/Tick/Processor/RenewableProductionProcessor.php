<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Planet\Model\Planet;
use App\Resource\Service\RenewableProductionConfig;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

/**
 * T-097a: Renewable-Production (W/F/O).
 *
 * Pro Tick: für jeden konfigurierten Producer-Building-Type:
 *  - Sum-of-Levels über alle ready Buildings dieses Typs auf dem Planeten
 *  - Output = Σlevel × baseRate
 *  - Storage-Cap-Stop (T-061): clamp am Planet-Storage-Cap der Resource
 *
 * Reihenfolge: läuft VOR PopulationConsumptionProcessor damit frische W/F sofort
 * konsumiert werden. Wiring via TickEngine `_instanceof` tagged_iterator —
 * Reihenfolge wird durch Service-Order in services.yaml bestimmt.
 */
readonly class RenewableProductionProcessor implements TickProcessorInterface
{
    public function __construct(
        private RenewableProductionConfig $config,
    ) {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        // T-065 Power-Throttle: bei Unter-Versorgung drosselt Renewable-Produktion.
        $powerThrottle = $planet->getPowerThrottleRatio($now);
        if ($powerThrottle <= 0.0) {
            return;
        }

        foreach ($this->config->entries() as $entry) {
            $totalLevel = 0;
            foreach ($planet->getBuildings() as $b) {
                if ($b->getType() !== $entry['building']) {
                    continue;
                }
                if (!$b->isReady($now)) {
                    continue;
                }
                $totalLevel += $b->getLevel();
            }
            if ($totalLevel === 0) {
                continue;
            }

            // T-065: Power-Throttle wirkt vor Storage-Cap-Clamp.
            $produced = (int) floor($totalLevel * $entry['baseRate'] * $powerThrottle);
            if ($produced <= 0) {
                continue;
            }
            $resource = $planet->ensureResource($entry['resource']);
            $cap = $planet->getStorageCapacity($entry['resource']);
            $room = max(0, $cap - $resource->getAmount());
            $actual = min($produced, $room);
            if ($actual <= 0) {
                continue;
            }
            $resource->setAmount($resource->getAmount() + $actual);
        }
    }
}
