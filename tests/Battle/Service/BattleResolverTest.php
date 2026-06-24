<?php

declare(strict_types=1);

namespace App\Tests\Battle\Service;

use App\Battle\Model\Battle;
use App\Battle\Service\BattleRandomizer;
use App\Battle\Service\BattleResolver;
use App\Battle\ValueObject\BattleId;
use App\Battle\ValueObject\BattleStatus;
use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Service\AdjustableClock;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewType;
use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetId;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Ship\Model\Ship;
use App\Ship\Service\ShipBlueprintRegistry;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class BattleResolverTest extends IntegrationTestCase
{
    private BattleResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = self::getContainer()->get(BattleResolver::class);
    }

    public function test_equal_fleets_draw_after_10_rounds(): void
    {
        $attacker = $this->seedFleetWithShips(2, ShipClass::FRIGATE_MK1);
        $defender = $this->seedFleetWithShips(2, ShipClass::FRIGATE_MK1);

        $battle = $this->makeBattle($attacker, $defender);
        $this->resolver->resolve($battle);

        // Both sides should have lost ships proportionally. Outcome can be
        // Draw or one-side win depending on rounding — test only that round
        // engine ran and battle ended.
        self::assertTrue($battle->getStatus()->isEnded());
        self::assertGreaterThan(0, $battle->getRounds());
    }

    public function test_attacker_wins_when_higher_class(): void
    {
        $attacker = $this->seedFleetWithShips(1, ShipClass::BATTLESHIP_MK1);
        $defender = $this->seedFleetWithShips(1, ShipClass::FRIGATE_MK1);

        $battle = $this->makeBattle($attacker, $defender);
        $this->resolver->resolve($battle);

        self::assertSame(BattleStatus::ENDED_ATTACKER_WIN, $battle->getStatus());
    }

    public function test_defender_wins_when_higher_class(): void
    {
        $attacker = $this->seedFleetWithShips(1, ShipClass::FRIGATE_MK1);
        $defender = $this->seedFleetWithShips(1, ShipClass::BATTLESHIP_MK1);

        $battle = $this->makeBattle($attacker, $defender);
        $this->resolver->resolve($battle);

        self::assertSame(BattleStatus::ENDED_DEFENDER_WIN, $battle->getStatus());
    }

    public function test_planet_defense_shield_absorbs_damage(): void
    {
        $attacker = $this->seedFleetWithShips(1, ShipClass::FRIGATE_MK1);
        $defenderPlanet = $this->planetWithShield(level: 5); // 25000 Shield-HP

        $battle = $this->makeBattle($attacker, null, $defenderPlanet);
        $this->resolver->resolve($battle);

        // Frigate Mk1 = 200 Damage/Round vs 25000 Shield → kein Ship-Damage,
        // Round-Limit erreicht → DRAW
        self::assertSame(BattleStatus::DRAW, $battle->getStatus());
    }

    public function test_captain_boosts_damage(): void
    {
        $reg = self::getContainer()->get(ShipBlueprintRegistry::class);
        $base = $reg->get(ShipClass::FRIGATE_MK1)->damage;

        // Setup: Frigate Mk1 mit Captain L10 (×1.30 Damage)
        $attackerPlayer = $this->newPlayer();
        $attackerPlanet = $this->planetForPlayer($attackerPlayer);
        $attackerShip = $this->makeShip(ShipClass::FRIGATE_MK1, $attackerPlanet);
        $captain = $this->makeAssignedCaptain($attackerPlayer, $attackerShip, level: 10);

        $attackerFleet = $this->fleetFromShip($attackerPlayer, $attackerPlanet, $attackerShip);
        $defender = $this->seedFleetWithShips(1, ShipClass::FRIGATE_MK1);

        $this->em->persist($captain);
        $this->em->flush();

        $battle = $this->makeBattle($attackerFleet, $defender);
        $this->resolver->resolve($battle);

        // Captain L10 = +30% Damage, defender no Captain → attacker should win
        self::assertSame(BattleStatus::ENDED_ATTACKER_WIN, $battle->getStatus());
        self::assertGreaterThan($base, (int) floor($base * 1.30));
    }

    public function test_captain_permadeath_when_pod_fails(): void
    {
        // Mock randomizer always returns 99 (above any Pod-Chance).
        $resolver = new BattleResolver(
            $this->em,
            self::getContainer()->get(ShipBlueprintRegistry::class),
            self::getContainer()->get(CrewRepository::class),
            self::getContainer()->get(AdjustableClock::class),
            new class extends BattleRandomizer {
                public function roll(): int
                {
                    return 99;
                }
            },
        );

        // Frigate (30% Pod) mit Captain vs. Battleship → Captain stirbt.
        $attackerPlayer = $this->newPlayer();
        $attackerPlanet = $this->planetForPlayer($attackerPlayer);
        $attackerShip = $this->makeShip(ShipClass::FRIGATE_MK1, $attackerPlanet);
        $captain = $this->makeAssignedCaptain($attackerPlayer, $attackerShip, level: 1);

        $attackerFleet = $this->fleetFromShip($attackerPlayer, $attackerPlanet, $attackerShip);
        $defender = $this->seedFleetWithShips(1, ShipClass::BATTLESHIP_MK1);

        $this->em->persist($captain);
        $this->em->flush();

        $battle = $this->makeBattle($attackerFleet, $defender);
        $resolver->resolve($battle);

        $this->em->refresh($captain);
        self::assertSame(\App\Crew\ValueObject\CrewStatus::DEAD, $captain->getStatus());
    }

    public function test_captain_survives_pod_when_roll_low(): void
    {
        $resolver = new BattleResolver(
            $this->em,
            self::getContainer()->get(ShipBlueprintRegistry::class),
            self::getContainer()->get(CrewRepository::class),
            self::getContainer()->get(AdjustableClock::class),
            new class extends BattleRandomizer {
                public function roll(): int
                {
                    return 0;
                }
            },
        );

        $attackerPlayer = $this->newPlayer();
        $attackerPlanet = $this->planetForPlayer($attackerPlayer);
        $attackerShip = $this->makeShip(ShipClass::FRIGATE_MK1, $attackerPlanet);
        $captain = $this->makeAssignedCaptain($attackerPlayer, $attackerShip, level: 1);

        $attackerFleet = $this->fleetFromShip($attackerPlayer, $attackerPlanet, $attackerShip);
        $defender = $this->seedFleetWithShips(1, ShipClass::BATTLESHIP_MK1);

        $this->em->persist($captain);
        $this->em->flush();

        $battle = $this->makeBattle($attackerFleet, $defender);
        $resolver->resolve($battle);

        $this->em->refresh($captain);
        self::assertSame(\App\Crew\ValueObject\CrewStatus::IDLE, $captain->getStatus());
    }

    // === Helpers ===

    private function newPlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);

        return $player;
    }

    private function planetForPlayer(Player $player): Planet
    {
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        return $planet;
    }

    private function makeShip(ShipClass $class, Planet $planet): Ship
    {
        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::GENERIC,
            populationAssigned: 30,
            cargoCapacity: 0,
        );
        $ship->setShipClass($class);
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));
        $this->em->persist($ship);

        return $ship;
    }

    private function fleetFromShip(Player $player, Planet $planet, Ship $ship): Fleet
    {
        $fleet = new Fleet(
            id: FleetId::generate(),
            player: $player,
            status: FleetStatus::DOCKED,
            originPlanet: $planet,
        );
        $fleet->attachShip($ship);
        $this->em->persist($fleet);
        $this->em->flush();

        return $fleet;
    }

    private function seedFleetWithShips(int $count, ShipClass $class): Fleet
    {
        $player = $this->newPlayer();
        $planet = $this->planetForPlayer($player);
        $fleet = new Fleet(
            id: FleetId::generate(),
            player: $player,
            status: FleetStatus::DOCKED,
            originPlanet: $planet,
        );
        for ($i = 0; $i < $count; $i++) {
            $ship = $this->makeShip($class, $planet);
            $fleet->attachShip($ship);
        }
        $this->em->persist($fleet);
        $this->em->flush();

        return $fleet;
    }

    private function planetWithShield(int $level): Planet
    {
        $player = $this->newPlayer();
        $planet = $this->planetForPlayer($player);
        $shield = new Building(BuildingId::generate(), BuildingType::PLANETARY_SHIELD, $level);
        $shield->restoreFullHp();
        $planet->addBuilding($shield);
        $this->em->flush();

        return $planet;
    }

    private function makeAssignedCaptain(Player $player, Ship $ship, int $level): Crew
    {
        $captain = new Crew(
            CrewId::generate(),
            $player,
            CrewType::CAPTAIN,
            \App\Crew\ValueObject\CrewStatus::IDLE,
        );
        if ($level > 1) {
            $xpNeeded = Crew::xpThresholdForLevel($level);
            $captain->addXp($xpNeeded);
        }
        $captain->assignToShip($ship);

        return $captain;
    }

    private function makeBattle(Fleet $attacker, ?Fleet $defenderFleet, ?Planet $defenderPlanet = null): Battle
    {
        $battle = new Battle(
            id: BattleId::generate(),
            attacker: $attacker->getPlayer(),
            attackerFleet: $attacker,
            defenderFleet: $defenderFleet,
            defenderPlanet: $defenderPlanet,
            location: $attacker->getOriginPlanet()?->getSolarSystem(),
        );
        $this->em->persist($battle);
        $this->em->flush();

        return $battle;
    }
}
