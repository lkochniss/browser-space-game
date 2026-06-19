<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Tick\Processor\ConstructionCompletionProcessor;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ConstructionCompletionProcessorTest extends TestCase
{
    public function test_in_progress_hub_does_not_contribute_to_cap(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $hub->setFinishedAt((new DateTimeImmutable())->modify('+1 hour'));
        $planet->addBuilding($hub, new DateTimeImmutable());

        (new ConstructionCompletionProcessor())->process($planet, new DateTimeImmutable());

        self::assertSame(100, $planet->getPopulation()->getCap());
    }

    public function test_completed_hub_contributes_after_processor_run(): void
    {
        // Hub mit finishedAt in der Zukunft. Erst noch in-progress.
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $finishesAt = (new DateTimeImmutable())->modify('+1 hour');
        $hub->setFinishedAt($finishesAt);
        $planet->addBuilding($hub, new DateTimeImmutable());

        // simuliere "Zeit vergeht" — nutze einen späteren $now
        $later = $finishesAt->modify('+1 minute');
        (new ConstructionCompletionProcessor())->process($planet, $later);

        self::assertSame(150, $planet->getPopulation()->getCap());
    }

    public function test_no_clock_means_in_progress_buildings_excluded(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $hub->setFinishedAt((new DateTimeImmutable())->modify('-1 hour'));
        $planet->addBuilding($hub, new DateTimeImmutable());

        // recalc(null) → konservativ: Hub mit finishedAt set wird NICHT counted
        (new ConstructionCompletionProcessor())->process($planet, null);

        self::assertSame(100, $planet->getPopulation()->getCap());
    }
}
