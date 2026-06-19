<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\Common\Service\SoftCapConfig;
use App\Planet\Model\Planet;
use App\Resource\Model\Resource;
use App\Resource\Service\PopulationConsumptionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;

readonly class PopulationConsumptionProcessor implements TickProcessorInterface
{
    public function __construct(
        private PopulationConsumptionConfig $config,
        private SoftCapConfig $softCap = new SoftCapConfig(),
    ) {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        $pop = $planet->getPopulation();
        $total = $pop->getTotal();

        if ($total === 0) {
            return;
        }

        $waterPerCap = $this->config->getPerCapita(ResourceType::WATER)
            * $planet->getType()->getConsumptionMultiplier(ResourceType::WATER);
        $foodPerCap = $this->config->getPerCapita(ResourceType::FOOD)
            * $planet->getType()->getConsumptionMultiplier(ResourceType::FOOD);

        $waterNeeded = (int) ceil($total * $waterPerCap);
        $foodNeeded = (int) ceil($total * $foodPerCap);

        $water = $planet->getResource(ResourceType::WATER);
        $food = $planet->getResource(ResourceType::FOOD);

        $waterShortage = max(0, $waterNeeded - $water->getAmount());
        $foodShortage = max(0, $foodNeeded - $food->getAmount());

        $this->consume($water, $waterNeeded);
        $this->consume($food, $foodNeeded);

        if ($waterShortage > 0 || $foodShortage > 0) {
            $deaths = $this->calculateDeaths($waterShortage, $waterPerCap, $foodShortage, $foodPerCap);
            $pop->kill(min($deaths, $total));
            return;
        }

        $delta = $this->logisticGrowthDelta($total, $pop->getCap(), $planet->getEffectivePopGrowthMultiplier());
        if ($delta > 0) {
            $pop->grow($delta);
        }
    }

    private function consume(Resource $resource, int $amount): void
    {
        $consumed = min($amount, $resource->getAmount());
        $resource->setAmount($resource->getAmount() - $consumed);
    }

    private function calculateDeaths(int $waterShortage, float $waterPerCap, int $foodShortage, float $foodPerCap): int
    {
        $deathsFromWater = $waterShortage > 0 && $waterPerCap > 0
            ? (int) ceil($waterShortage / $waterPerCap)
            : 0;
        $deathsFromFood = $foodShortage > 0 && $foodPerCap > 0
            ? (int) ceil($foodShortage / $foodPerCap)
            : 0;

        return max($deathsFromWater, $deathsFromFood);
    }

    private function logisticGrowthDelta(int $total, int $cap, float $typeGrowthMultiplier): int
    {
        if ($cap <= 0 || $total >= $cap) {
            return 0;
        }

        // T-063: Effective Rate = base × PlanetType-Bonus (× sizeFactor)
        // T-151: Soft-Cap-Multiplier ab 1M Pop drosselt zusätzlich.
        $rate = $this->config->getLogisticGrowthRate()
            * $typeGrowthMultiplier
            * $this->softCap->popGrowthMultiplier($total);
        $delta = $rate * $total * (1.0 - $total / $cap);

        return (int) round($delta);
    }
}

