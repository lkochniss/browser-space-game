<?php

declare(strict_types=1);

namespace App\Battle\Service;

use App\Battle\Exception\InvalidBattleTargetException;
use App\Battle\Model\Battle;
use App\Battle\ValueObject\BattleId;
use App\Fleet\Model\Fleet;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetId;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-103: Battle starten — Validation (Targets, System-Match, Non-Empty),
 * Battle-Entity erstellen, Resolver synchron ausführen.
 */
readonly class StartBattleCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FleetRepository $fleetRepo,
        private PlanetRepository $planetRepo,
        private BattleResolver $resolver,
    ) {
    }

    public function __invoke(
        FleetId $attackerFleetId,
        ?FleetId $defenderFleetId,
        ?PlanetId $defenderPlanetId,
    ): Battle {
        if ($defenderFleetId === null && $defenderPlanetId === null) {
            throw InvalidBattleTargetException::bothNull();
        }
        if ($defenderFleetId !== null && $defenderPlanetId !== null) {
            throw InvalidBattleTargetException::bothSet();
        }

        $attacker = $this->fleetRepo->find($attackerFleetId)
            ?? throw new \DomainException(sprintf('Attacker-Fleet %s not found', $attackerFleetId));
        if ($attacker->getShips()->isEmpty()) {
            throw InvalidBattleTargetException::emptyFleet('Attacker');
        }

        $defenderFleet = null;
        $defenderPlanet = null;
        $location = $this->fleetLocation($attacker);

        if ($defenderFleetId !== null) {
            $defenderFleet = $this->fleetRepo->find($defenderFleetId)
                ?? throw new \DomainException(sprintf('Defender-Fleet %s not found', $defenderFleetId));
            if ($defenderFleet->getShips()->isEmpty()) {
                throw InvalidBattleTargetException::emptyFleet('Defender');
            }
            $defenderLocation = $this->fleetLocation($defenderFleet);
            if ($location?->getId()->equals($defenderLocation?->getId()) !== true) {
                throw InvalidBattleTargetException::notInSameSystem();
            }
        } else {
            $defenderPlanet = $this->planetRepo->find($defenderPlanetId)
                ?? throw new \DomainException(sprintf('Defender-Planet %s not found', $defenderPlanetId));
            $planetSystem = $defenderPlanet->getSolarSystem();
            if ($location?->getId()->equals($planetSystem?->getId()) !== true) {
                throw InvalidBattleTargetException::notInSameSystem();
            }
        }

        $battle = new Battle(
            id: BattleId::generate(),
            attacker: $attacker->getPlayer(),
            attackerFleet: $attacker,
            defenderFleet: $defenderFleet,
            defenderPlanet: $defenderPlanet,
            location: $location,
        );
        $this->em->persist($battle);
        $this->em->flush();

        $this->resolver->resolve($battle);

        return $battle;
    }

    private function fleetLocation(Fleet $fleet): ?SolarSystem
    {
        $origin = $fleet->getOriginPlanet();
        if ($origin !== null) {
            return $origin->getSolarSystem();
        }
        $target = $fleet->getTargetPlanet();

        return $target?->getSolarSystem();
    }
}
