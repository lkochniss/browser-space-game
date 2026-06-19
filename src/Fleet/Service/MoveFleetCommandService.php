<?php

declare(strict_types=1);

namespace App\Fleet\Service;

use App\Common\Interface\ClockInterface;
use App\Fleet\Exception\FleetAlreadyInTransitException;
use App\Fleet\Exception\FleetNotFoundException;
use App\Fleet\Exception\InterSystemTravelLockedException;
use App\Fleet\Exception\SameOriginAndTargetException;
use App\Fleet\Model\Fleet;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetId;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Exception\PlanetNotFoundException;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Research\Repository\PlayerResearchRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-017: Schickt eine Fleet von ihrem origin zum targetPlanet.
 *
 * Effekt:
 * - status: DOCKED → IN_TRANSIT
 * - departedAt = now, arrivedAt = now + duration
 * - duration via FleetMovementConfig (sameSystem? × Speed-Adjusted)
 * - alle Schiffe der Fleet: planet=null (während Flug)
 *
 * FleetArrivalService (Tick) materialisiert die Ankunft.
 */
readonly class MoveFleetCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FleetRepository $fleetRepository,
        private PlanetRepository $planetRepository,
        private FleetMovementConfig $movementConfig,
        private ClockInterface $clock,
        private PlayerResearchRepository $playerResearchRepository,
    ) {
    }

    public function __invoke(FleetId $fleetId, PlanetId $targetPlanetId): Fleet
    {
        $fleet = $this->fleetRepository->find($fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException($fleetId);
        }

        if ($fleet->isInTransit()) {
            throw new FleetAlreadyInTransitException($fleetId);
        }

        $target = $this->planetRepository->find($targetPlanetId);
        if ($target === null) {
            throw new PlanetNotFoundException($targetPlanetId);
        }

        $origin = $fleet->getOriginPlanet();
        if ($origin !== null && $origin->getId()->equals($targetPlanetId)) {
            throw new SameOriginAndTargetException($targetPlanetId);
        }

        $sameSystem = $this->isSameSystem($origin, $target);
        if (!$sameSystem) {
            // T-026: Inter-System-Travel braucht FTL (ftl_hyperdrive L1+)
            $owner = $fleet->getPlayer();
            $ftlLevel = $this->playerResearchRepository
                ->findOneByPlayerAndSlug($owner, 'ftl_hyperdrive')
                ?->getLevel() ?? 0;
            if ($ftlLevel < 1) {
                throw new InterSystemTravelLockedException();
            }
        }
        $duration = $this->movementConfig->computeDurationSeconds($sameSystem, $fleet->getMinSpeed());

        $now = $this->clock->now();
        $arrival = $now->add(new DateInterval(sprintf('PT%dS', $duration)));

        $fleet->setStatus(FleetStatus::IN_TRANSIT);
        $fleet->setTargetPlanet($target);
        $fleet->setDepartedAt($now);
        $fleet->setArrivedAt($arrival);

        // Schiffe haben während Flug keinen Planet (siehe T-012 Ship.planet nullable)
        foreach ($fleet->getShips() as $ship) {
            $ship->setPlanet(null);
        }

        $this->em->flush();

        return $fleet;
    }

    private function isSameSystem(?\App\Planet\Model\Planet $origin, \App\Planet\Model\Planet $target): bool
    {
        if ($origin === null) {
            return false;
        }
        $originSystem = $origin->getSolarSystem();
        $targetSystem = $target->getSolarSystem();

        if ($originSystem === null || $targetSystem === null) {
            // Falls SolarSystem nicht gesetzt: defaultiere zu inter-system (konservativ).
            return false;
        }

        return $originSystem->getId()->equals($targetSystem->getId());
    }
}
