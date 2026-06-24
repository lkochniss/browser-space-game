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
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Service\RefinementConfig;
use App\Resource\Service\RenewableProductionConfig;
use App\Resource\Service\ResourceProductionConfig;
use App\Resource\ValueObject\ResourceType;
use App\Tick\Policy\BasicResourceExtractionPolicy;
use App\Tick\Processor\RefinementProductionProcessor;
use App\Tick\Processor\RenewableProductionProcessor;
use App\Tick\Processor\ResourceProductionProcessor;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * T-065 Power-Throttle integration tests pro Production-Mechanik (Mining,
 * Refinement, Renewable). Bei `produced < consumed` drosselt der Output
 * linear via `produced / consumed`. Bei `produced == 0` → kein Output.
 */
final class PowerThrottleProductionTest extends TestCase
{
    public function test_mining_throttled_proportionally_at_half_power(): void
    {
        // IRON_MINE L1 = 3 consumed. HUB L1 = 75 produced. Add SHIPYARD L9 = 135 consumed.
        // Total consumed = 75+1+135 = need to redo numbers cleanly.
        // Sauberer: HUB L1 = 75 produced (1 self). IRON_MINE L24 → 72 consumed.
        // Total consumed = 1 (HUB) + 72 (Mines) = 73. Ratio = 75/73 = 1.0 (cap). Nicht gut.
        //
        // Setup für Half-Power: produced ~ consumed/2.
        // HUB L1 = 75 produced. SHIPYARD L9 = 135 consumed. HUB self 1.
        // → consumed = 136, produced = 75, ratio = 75/136 ≈ 0.551.
        // IRON_MINE L1 → desired = 10. throttled = 10 × 0.551 = 5.51 → 5 extracted.
        $planet = $this->mineablePlanet(
            mineLevel: 1,
            depositAmount: 1000,
            hubLevel: 1,
            extraConsumer: BuildingType::SHIPYARD,
            extraConsumerLevel: 9,
        );

        $this->miningProcessor()->process($planet);

        // 5 extracted bei ratio ≈ 0.551
        self::assertSame(5, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(995, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_mining_zero_power_no_output(): void
    {
        $planet = $this->mineablePlanet(mineLevel: 1, depositAmount: 1000, hubLevel: 0);

        $this->miningProcessor()->process($planet);

        self::assertSame(0, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(1000, $planet->getResourceDeposit(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_mining_full_power_unaffected(): void
    {
        $planet = $this->mineablePlanet(mineLevel: 1, depositAmount: 1000, hubLevel: 1000);

        $this->miningProcessor()->process($planet);

        // L1 mine produziert volle 10
        self::assertSame(10, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_refinement_throttled_proportionally(): void
    {
        // L3 IRON_SMELTER → desired = 3 BARs. Power-Throttle 0.5 → floor(3×0.5)=1.
        // HUB L1 = 75 produced. IRON_SMELTER L3 = 24 consumed + HUB self 1 = 25.
        // Wir brauchen ratio ≈ 0.5 → produced ≈ consumed/2.
        // Setup: HUB L1 produced=75. Add SHIPYARD L9 (135 consumed) + HUB self 1 + Smelter 24 = 160.
        // Ratio = 75/160 ≈ 0.469. floor(3 × 0.469) = 1.
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, 1));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::SHIPYARD, 9));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 3));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_BAR));

        $this->refinementProcessor()->process($planet);

        // 1 BAR produziert: 2 iron + 1 coal verbraucht
        self::assertSame(1, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(98, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        self::assertSame(99, $planet->getResource(ResourceType::COAL)->getAmount());
    }

    public function test_refinement_zero_power_no_output(): void
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_SMELTER, 1));
        $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 100));
        $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 100));
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_BAR));

        $this->refinementProcessor()->process($planet);

        self::assertSame(0, $planet->getResource(ResourceType::IRON_BAR)->getAmount());
        self::assertSame(100, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
    }

    public function test_renewable_throttled_proportionally(): void
    {
        // WATER_RECLAIMER L4 base = 4 × 10 = 40. Throttle 0.5 → floor(20) = 20.
        // HUB L1 produced=75. WATER_RECLAIMER L4 consumed=4 + HUB self 1 = 5.
        // Ratio = 75/5 = 15 → cap 1.0. Nicht gut für Half-Test.
        // Setup für ~Half: HUB L1=75 produced. SHIPYARD L9 = 135 consumed + HUB 1 + W_R L4 = 4 → 140.
        // Ratio = 75/140 ≈ 0.536. floor(40 × 0.536) = 21.
        $now = new DateTimeImmutable('-1 minute');
        $planet = Planet::generatePlanet(PlanetId::generate());

        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $hub->setFinishedAt($now);
        $planet->addBuilding($hub);

        $shipyard = new Building(BuildingId::generate(), BuildingType::SHIPYARD, 9);
        $shipyard->setFinishedAt($now);
        $planet->addBuilding($shipyard);

        $wr = new Building(BuildingId::generate(), BuildingType::WATER_RECLAIMER, 4);
        $wr->setFinishedAt($now);
        $planet->addBuilding($wr);

        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 0));

        $this->renewableProcessor()->process($planet, new DateTimeImmutable('now'));

        self::assertSame(21, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    public function test_renewable_zero_power_no_output(): void
    {
        $now = new DateTimeImmutable('-1 minute');
        $planet = Planet::generatePlanet(PlanetId::generate());
        $wr = new Building(BuildingId::generate(), BuildingType::WATER_RECLAIMER, 3);
        $wr->setFinishedAt($now);
        $planet->addBuilding($wr);
        $planet->addResource(Resource::generateWithAmount(ResourceType::WATER, 0));

        $this->renewableProcessor()->process($planet, new DateTimeImmutable('now'));

        self::assertSame(0, $planet->getResource(ResourceType::WATER)->getAmount());
    }

    private function mineablePlanet(
        int $mineLevel,
        int $depositAmount,
        int $hubLevel,
        ?BuildingType $extraConsumer = null,
        int $extraConsumerLevel = 0,
    ): Planet {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->addResource(Resource::generateEmptyResource(ResourceType::IRON_ORE));
        $planet->addDeposit(ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, $depositAmount));
        $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::IRON_MINE, $mineLevel));
        if ($hubLevel > 0) {
            $planet->addBuilding(new Building(BuildingId::generate(), BuildingType::HUB, $hubLevel));
        }
        if ($extraConsumer !== null && $extraConsumerLevel > 0) {
            $planet->addBuilding(new Building(BuildingId::generate(), $extraConsumer, $extraConsumerLevel));
        }

        return $planet;
    }

    private function miningProcessor(): ResourceProductionProcessor
    {
        $map = new ResourceBuildingMap();

        return new ResourceProductionProcessor(
            new BasicResourceExtractionPolicy($map),
            $map,
            new ResourceProductionConfig(),
            new ResourceProductionHelper($map),
        );
    }

    private function refinementProcessor(): RefinementProductionProcessor
    {
        return new RefinementProductionProcessor(new RefinementConfig());
    }

    private function renewableProcessor(): RenewableProductionProcessor
    {
        return new RenewableProductionProcessor(new RenewableProductionConfig());
    }
}
