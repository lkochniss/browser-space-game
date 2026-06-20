<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Command\CancelBuildCommand;
use App\Building\Command\UpgradeBuildingCommand;
use App\Building\Exception\BuildingNotInProgressException;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\Faction\Service\FactionSeedService;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\ValueObject\PlayerId;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Tests\Integration\IntegrationTestCase;

final class CancelBuildCommandServiceTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_cancel_initial_build_removes_building_and_refunds(): void
    {
        $planet = $this->bootstrapPlanet();
        $iron = $planet->getResource(ResourceType::IRON_ORE)->getAmount();
        $popFreeBefore = $planet->getPopulation()->getFree();

        // HUB cost = 100 IRON_ORE + 50 COAL + 10 pop
        $built = $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));

        // Während Bauphase: Resource abgezogen, Pop assigned
        $hasHub = false;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getType() === BuildingType::HUB) {
                $hasHub = true;
                break;
            }
        }
        self::assertTrue($hasHub);
        self::assertSame($iron - 100, $planet->getResource(ResourceType::IRON_ORE)->getAmount());

        // Cancel
        $result = $this->bus->dispatch(new CancelBuildCommand($planet->getId(), $built->getId()));
        self::assertNull($result, 'Initial-Cancel returns null (Building gelöscht)');

        // Building weg
        $found = false;
        foreach ($planet->getBuildings() as $b) {
            if ($b->getId()->equals($built->getId())) {
                $found = true;
                break;
            }
        }
        self::assertFalse($found, 'Building nach Cancel weg');

        // Refund: 50% Resource + voll Pop
        self::assertSame($iron - 50, $planet->getResource(ResourceType::IRON_ORE)->getAmount(), '50% Iron-Refund');
        self::assertSame($popFreeBefore, $planet->getPopulation()->getFree(), 'Pop voll released');
    }

    public function test_cancel_upgrade_reverts_level_and_refunds(): void
    {
        $planet = $this->bootstrapPlanet();

        // Initial HUB bauen + sofort fertigstellen
        $hub = $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        $clock = self::getContainer()->get(AdjustableClock::class);
        $clock->advanceSeconds(99999);
        $this->em->refresh($hub);

        // Upgrade L1→L2 starten (cost = 100×2 = 200 IRON, 50×2=100 COAL, pop 10×2=20)
        $ironBefore = $planet->getResource(ResourceType::IRON_ORE)->getAmount();
        $popFreeBefore = $planet->getPopulation()->getFree();
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $hub->getId()));

        self::assertSame(2, $hub->getLevel(), 'L2 während Upgrade');
        self::assertFalse($hub->isReady($clock->now()));

        // Cancel
        $result = $this->bus->dispatch(new CancelBuildCommand($planet->getId(), $hub->getId()));
        self::assertNotNull($result);
        self::assertSame(1, $result->getLevel(), 'L1 nach Upgrade-Cancel');
        self::assertNull($result->getFinishedAt(), 'finishedAt cleared → ready');
        self::assertTrue($result->isReady($clock->now()));

        // Upgrade-Cost 200 IRON abgezogen, Refund 50% = 100 zurück → Netto -100
        self::assertSame($ironBefore - 100, $planet->getResource(ResourceType::IRON_ORE)->getAmount());
        // Upgrade-Pop (20) released → free wieder wie vorher
        self::assertSame($popFreeBefore, $planet->getPopulation()->getFree());
    }

    public function test_cancel_finished_building_throws(): void
    {
        $planet = $this->bootstrapPlanet();
        $hub = $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::HUB));
        // Clock voraus → fertig
        self::getContainer()->get(AdjustableClock::class)->advanceSeconds(99999);
        $this->em->refresh($hub);

        $this->expectException(BuildingNotInProgressException::class);
        $this->bus->dispatch(new CancelBuildCommand($planet->getId(), $hub->getId()));
    }

    private function bootstrapPlanet(): \App\Planet\Model\Planet
    {
        self::getContainer()->get(FactionSeedService::class)->seed();
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));
        $planet = self::getContainer()->get(PlanetRepository::class)->find($planetId);

        // Resources & Pop großzügig
        try {
            $planet->getResource(ResourceType::IRON_ORE)->setAmount(5000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_ORE, 5000));
        }
        try {
            $planet->getResource(ResourceType::COAL)->setAmount(5000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::COAL, 5000));
        }
        $planet->getPopulation()->grow(500);
        $this->em->flush();

        return $planet;
    }
}
