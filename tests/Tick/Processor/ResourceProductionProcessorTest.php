<?php

declare(strict_types=1);

namespace App\Tests\Tick\Processor;

use App\Building\Model\Building;
use App\Building\Service\ResourceBuildingMap;
use App\Building\Service\ResourceProductionHelper;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use App\Tick\Processor\ResourceProductionProcessor;
use PHPUnit\Framework\TestCase;

final class ResourceProductionProcessorTest extends TestCase
{
    private ResourceProductionProcessor $processor;

    protected function setUp(): void
    {
        $map = new ResourceBuildingMap();
        $this->processor = new ResourceProductionProcessor(
            new BasicResourceExtractionPolicy($map),
            $map,
            new ResourceProductionConfig(),
            new ResourceProductionHelper($map),
        );
    }

    public function test_level_1_building_produces_one_times_base(): void
    {
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 1000);

        $this->processor->process($planet);

        self::assertSame(990, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(10, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_level_3_building_produces_three_times_base(): void
    {
        $planet = $this->makePlanet(buildingLevel: 3, depositAmount: 1000);

        $this->processor->process($planet);

        self::assertSame(970, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(30, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_extraction_clamped_to_deposit_amount(): void
    {
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 4);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(4, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_empty_deposit_yields_no_extraction(): void
    {
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 0);

        $this->processor->process($planet);

        self::assertSame(0, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_storage_cap_stops_extraction_when_full(): void
    {
        // T-177 Volume: base 5000 + IRON_MINE L1 (50) = 5050 m³.
        // IRON_ORE multi = 2 m³ → max 2525 Einheiten. Pre-fill 2525 → kein Platz.
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 10_000);
        $planet->getResource(ResourceType::IRON_ORE)->setAmount(2525);

        $this->processor->process($planet);

        self::assertSame(10_000, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(2525, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_storage_cap_partially_clamps_extraction(): void
    {
        // T-177 Volume: 5050 m³ cap, IRON_ORE multi=2. Pre-fill 2520 → 10 m³ frei
        // → 5 IRON_ORE addable. Mine L1 produziert 10 desired → nur 5 extracted.
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 10_000);
        $planet->getResource(ResourceType::IRON_ORE)->setAmount(2520);

        $this->processor->process($planet);

        self::assertSame(9_995, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(2525, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_in_progress_mine_does_not_produce(): void
    {
        // T-062: Mine mit finishedAt in Zukunft → not ready → keine Production
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 1000);
        $mine = $planet->getBuildings()->first();
        $mine->setFinishedAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->processor->process($planet, new \DateTimeImmutable());

        self::assertSame(1000, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_completed_mine_produces_after_finishedAt(): void
    {
        $planet = $this->makePlanet(buildingLevel: 1, depositAmount: 1000);
        $mine = $planet->getBuildings()->first();
        $mine->setFinishedAt((new \DateTimeImmutable())->modify('-1 second'));

        $this->processor->process($planet, new \DateTimeImmutable());

        self::assertSame(990, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(10, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_barren_planet_mining_bonus_applies(): void
    {
        // BARREN MEDIUM Iron-Mining-Multiplier = 1.5 → L1 mine produces 15 instead of 10
        // Cap = base 100 (FINITE) + mine L1 contribution 100 = 200 → 15 fits
        $planet = $this->makePlanetWithType(
            buildingLevel: 1,
            depositAmount: 1000,
            type: \App\Planet\ValueObject\PlanetType::BARREN,
            size: \App\Planet\ValueObject\PlanetSize::MEDIUM,
        );

        $this->processor->process($planet);

        self::assertSame(985, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(15, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_gas_giant_mining_blocked(): void
    {
        // GAS_GIANT mining-multiplier = 0 → no production regardless of deposit/mine
        $planet = $this->makePlanetWithType(
            buildingLevel: 3,
            depositAmount: 1000,
            type: \App\Planet\ValueObject\PlanetType::GAS_GIANT,
            size: \App\Planet\ValueObject\PlanetSize::MEDIUM,
        );

        $this->processor->process($planet);

        self::assertSame(1000, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    private function makePlanet(int $buildingLevel, int $depositAmount): Planet
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        // T-065 Power: HUB L1000 → 25050 produced, deckt jeden Test-Setup.
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1000));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, $buildingLevel));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, $depositAmount));

        return $planet;
    }

    private function makePlanetWithType(
        int $buildingLevel,
        int $depositAmount,
        \App\Planet\ValueObject\PlanetType $type,
        \App\Planet\ValueObject\PlanetSize $size,
    ): Planet {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate(), $type, $size);
        $player->claimPlanet($planet);

        // T-065 Power: HUB L1000 → 25050 produced.
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1000));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, $buildingLevel));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, $depositAmount));

        return $planet;
    }
}
