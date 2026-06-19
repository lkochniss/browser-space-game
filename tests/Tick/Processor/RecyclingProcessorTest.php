<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\Service\RecyclingTable;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\Randomizer;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Processor\RecyclingProcessor;
use PHPUnit\Framework\TestCase;

final class RecyclingProcessorTest extends TestCase
{
    public function test_no_op_without_recycling_plant(): void
    {
        $planet = $this->makePlanet(debrisLow: 10);

        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer([1]));
        $processor->process($planet);

        self::assertSame(10, $planet->getResource(ResourceType::DEBRIS_LOW)->getAmount());
    }

    public function test_no_op_without_debris(): void
    {
        $planet = $this->makePlanet(debrisLow: 0, recyclingLevel: 1);

        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer([1]));
        $processor->process($planet, new \DateTimeImmutable('now'));

        self::assertSame(0, $this->amount($planet, ResourceType::DEBRIS_LOW));
        self::assertSame(0, $this->amount($planet, ResourceType::IRON_ORE));
    }

    public function test_consumes_units_per_level(): void
    {
        // Lvl 1 → 2 Units pro Tick
        $planet = $this->makePlanet(debrisLow: 10, recyclingLevel: 1);

        // Stub: always roll 1 → first entry (iron_ore 5-15), amount = 5 (min)
        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer([1, 5, 1, 5]));
        $processor->process($planet, new \DateTimeImmutable('now'));

        self::assertSame(8, $planet->getResource(ResourceType::DEBRIS_LOW)->getAmount(), 'consumed 2 of 10');
        self::assertSame(10, $planet->getResource(ResourceType::IRON_ORE)->getAmount(), 'produced 5+5 iron_ore');
    }

    public function test_higher_level_consumes_more(): void
    {
        // Lvl 3 → 6 Units pro Tick
        $planet = $this->makePlanet(debrisLow: 10, recyclingLevel: 3);

        // 6 rolls × 2 calls (weight + amount). Always pick last entry (null) — output: nothing
        // Weights: 70+20+10=100; roll 95 → "null" entry. amount-call irrelevant for null entry.
        $sequence = [];
        for ($i = 0; $i < 6; $i++) {
            $sequence[] = 95;  // hit "null" entry
        }
        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer($sequence));
        $processor->process($planet, new \DateTimeImmutable('now'));

        self::assertSame(4, $this->amount($planet, ResourceType::DEBRIS_LOW), 'consumed 6 of 10');
        self::assertSame(0, $this->amount($planet, ResourceType::IRON_ORE), 'no output (null entries)');
    }

    public function test_consumes_low_then_medium(): void
    {
        // Lvl 2 → 4 Units. Hat 1 LOW + 5 MEDIUM. Sollte 1 LOW + 3 MEDIUM konsumieren.
        $planet = $this->makePlanet(debrisLow: 1, debrisMedium: 5, recyclingLevel: 2);

        // 4 rolls × 2 = 8 nums. Always weight=95 (null) und amount=0 (won't be reached for null).
        $sequence = [];
        for ($i = 0; $i < 4; $i++) {
            $sequence[] = 95;
        }
        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer($sequence));
        $processor->process($planet, new \DateTimeImmutable('now'));

        self::assertSame(0, $planet->getResource(ResourceType::DEBRIS_LOW)->getAmount(), 'all 1 LOW consumed first');
        self::assertSame(2, $planet->getResource(ResourceType::DEBRIS_MEDIUM)->getAmount(), 'remaining 3 budget consumed MEDIUM (5→2)');
    }

    public function test_unfinished_recycling_plant_skipped(): void
    {
        $planet = $this->makePlanet(debrisLow: 10, recyclingLevel: 0);

        // Add unfinished recycling-plant (finishedAt in future)
        $b = new Building(BuildingId::generate(), BuildingType::RECYCLING_PLANT, 1);
        $b->setFinishedAt(new \DateTimeImmutable('+1 hour'));
        $planet->addBuilding($b);

        $now = new \DateTimeImmutable('now');
        $processor = new RecyclingProcessor(new RecyclingTable(), $this->fixedRandomizer([1, 5]));
        $processor->process($planet, $now);

        self::assertSame(10, $planet->getResource(ResourceType::DEBRIS_LOW)->getAmount(), 'unfinished plant ignored');
    }

    private function amount(Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $r) {
            if ($r->getType() === $type) {
                return $r->getAmount();
            }
        }

        return 0;
    }

    private function makePlanet(int $debrisLow = 0, int $debrisMedium = 0, int $recyclingLevel = 0): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        if ($debrisLow > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::DEBRIS_LOW, $debrisLow));
        }
        if ($debrisMedium > 0) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::DEBRIS_MEDIUM, $debrisMedium));
        }
        if ($recyclingLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::RECYCLING_PLANT, $recyclingLevel);
            // Make ready by setting finishedAt to past
            $b->setFinishedAt(new \DateTimeImmutable('-1 minute'));
            $planet->addBuilding($b);
        }

        return $planet;
    }

    /**
     * @param list<int> $sequence
     */
    private function fixedRandomizer(array $sequence): Randomizer
    {
        return new class ($sequence) extends Randomizer {
            /** @var list<int> */
            private array $sequence;
            private int $idx = 0;

            public function __construct(array $sequence)
            {
                $this->sequence = $sequence;
            }

            public function intBetween(int $min, int $max): int
            {
                if (!isset($this->sequence[$this->idx])) {
                    return $min;
                }

                return $this->sequence[$this->idx++];
            }
        };
    }
}
