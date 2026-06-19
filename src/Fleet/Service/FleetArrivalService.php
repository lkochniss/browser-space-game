<?php

declare(strict_types=1);

namespace App\Fleet\Service;

use App\Common\Interface\ClockInterface;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-017 Tick-Service. Materialisiert Ankünfte aller Fleets deren arrivedAt
 * <= now ist.
 *
 * Nicht TickProcessorInterface (ist Planet-zentriert), sondern globaler Service.
 * T-044 Tick-Scheduler ruft das via Cron.
 *
 * Pro angekommener Fleet:
 * - Schiffe: planet = targetPlanet
 * - Fleet: status = DOCKED, originPlanet = targetPlanet, targetPlanet = null,
 *   departedAt/arrivedAt bleiben für History
 */
readonly class FleetArrivalService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FleetRepository $fleetRepository,
        private ClockInterface $clock,
    ) {
    }

    public function resolveArrivedFleets(): int
    {
        $now = $this->clock->now();
        $arrived = $this->fleetRepository->findArrivedFleets($now);

        foreach ($arrived as $fleet) {
            $target = $fleet->getTargetPlanet();
            if ($target === null) {
                // Defensiv: ohne target können wir nicht docken. Reset auf origin.
                $fleet->setStatus(FleetStatus::DOCKED);
                continue;
            }

            foreach ($fleet->getShips() as $ship) {
                $ship->setPlanet($target);
            }

            $fleet->setStatus(FleetStatus::DOCKED);
            $fleet->setOriginPlanet($target);
            $fleet->setTargetPlanet(null);
        }

        $this->em->flush();

        return count($arrived);
    }
}
