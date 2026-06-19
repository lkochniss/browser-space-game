<?php

declare(strict_types=1);

namespace App\Probe\Service;

use App\Probe\ValueObject\ProbeType;
use App\Resource\ValueObject\ResourceType;
use LogicException;

/**
 * T-013 Foundation: Cost + Bauzeit pro ProbeType.
 *
 * - SYSTEM: günstig + schnell (Massenware)
 * - ORBITAL: mittel (bleibt im Orbit)
 * - DEEP_SCAN: teuer + langsam (Endgame-Tier)
 *
 * Späteres Ticket (T-027 Planetologie) kann Forschungs-Locks ergänzen.
 */
class ProbeCostConfig
{
    /** @var array<string, array{resources: array<string,int>, durationSeconds: int}> */
    private array $configs;

    public function __construct()
    {
        $this->configs = [
            ProbeType::SYSTEM->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 30,
                ],
                'durationSeconds' => 600, // 10min
            ],
            ProbeType::ORBITAL->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 80,
                    ResourceType::SILICON->value => 30,
                ],
                'durationSeconds' => 1200, // 20min
            ],
            ProbeType::DEEP_SCAN->value => [
                'resources' => [
                    ResourceType::IRON_BAR->value => 200,
                    ResourceType::SILICON->value => 80,
                    ResourceType::COPPER_ORE->value => 50,
                ],
                'durationSeconds' => 3600, // 60min
            ],
        ];
    }

    /**
     * @return array<string,int>
     */
    public function getResourceCost(ProbeType $type): array
    {
        if (!isset($this->configs[$type->value])) {
            throw new LogicException(sprintf('No cost configured for ProbeType "%s"', $type->value));
        }

        return $this->configs[$type->value]['resources'];
    }

    public function getDurationSeconds(ProbeType $type): int
    {
        if (!isset($this->configs[$type->value])) {
            throw new LogicException(sprintf('No duration configured for ProbeType "%s"', $type->value));
        }

        return $this->configs[$type->value]['durationSeconds'];
    }
}
