<?php

declare(strict_types=1);

namespace App\Tests\Building\Service;

use App\Building\Command\RepairBuildingCommand;
use App\Building\Exception\BuildingNotDamagedException;
use App\Building\Exception\RepairCooldownActiveException;
use App\Building\Model\Building;
use App\Building\Service\RepairBuildingCommandService;
use App\Building\ValueObject\BuildingId;
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

/**
 * T-068 Repair-Mechanik: 30% Cost, 24h Cooldown, currentHp → maxHp.
 */
final class RepairBuildingCommandServiceTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;
    private PlanetRepository $planets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
        $this->planets = self::getContainer()->get(PlanetRepository::class);
    }

    public function test_repair_restores_full_hp_and_debits_resources(): void
    {
        $planet = $this->bootstrapPlanet();
        $turret = $this->addDamagedTurret($planet, level: 2, damageApplied: 250);

        $steelBefore = $planet->getResource(ResourceType::STEEL)->getAmount();
        $this->em->flush();

        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));

        $this->em->refresh($turret);
        self::assertSame($turret->computeMaxHp(), $turret->getCurrentHp());
        // Repair cost = ceil(300 × 0.30) = 90 Steel
        self::assertSame($steelBefore - 90, $planet->getResource(ResourceType::STEEL)->getAmount());
    }

    public function test_repair_undamaged_building_throws(): void
    {
        $planet = $this->bootstrapPlanet();
        $turret = $this->addDamagedTurret($planet, level: 1, damageApplied: 0);

        $this->expectException(BuildingNotDamagedException::class);
        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));
    }

    public function test_repair_cooldown_blocks_immediate_re_repair(): void
    {
        $planet = $this->bootstrapPlanet();
        $turret = $this->addDamagedTurret($planet, level: 1, damageApplied: 100);
        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));

        // Beschädige direkt wieder
        $turret->takeDamage(50);
        $this->em->flush();

        $this->expectException(RepairCooldownActiveException::class);
        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));
    }

    public function test_repair_after_cooldown_succeeds(): void
    {
        $planet = $this->bootstrapPlanet();
        $turret = $this->addDamagedTurret($planet, level: 1, damageApplied: 100);
        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));

        $turret->takeDamage(50);
        $this->em->flush();
        // Advance Clock 24h+
        self::getContainer()->get(AdjustableClock::class)->advanceSeconds(86401);

        $this->bus->dispatch(new RepairBuildingCommand($planet->getId(), $turret->getId()));

        $this->em->refresh($turret);
        self::assertSame($turret->computeMaxHp(), $turret->getCurrentHp());
    }

    private function addDamagedTurret(\App\Planet\Model\Planet $planet, int $level, int $damageApplied): Building
    {
        $turret = new Building(BuildingId::generate(), BuildingType::DEFENSE_TURRET, $level);
        $turret->restoreFullHp();
        if ($damageApplied > 0) {
            $turret->takeDamage($damageApplied);
        }
        $planet->addBuilding($turret);
        $this->em->flush();

        return $turret;
    }

    private function bootstrapPlanet(): \App\Planet\Model\Planet
    {
        self::getContainer()->get(FactionSeedService::class)->seed();
        $playerId = PlayerId::generate();
        $planetId = PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));
        $planet = $this->planets->find($planetId);

        try {
            $planet->getResource(ResourceType::STEEL)->setAmount(5000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::STEEL, 5000));
        }
        try {
            $planet->getResource(ResourceType::IRON_BAR)->setAmount(5000);
        } catch (\Throwable) {
            $planet->addResource(Resource::generateWithAmount(ResourceType::IRON_BAR, 5000));
        }
        $planet->getPopulation()->grow(500);
        $this->em->flush();

        return $planet;
    }
}
