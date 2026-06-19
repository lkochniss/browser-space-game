<?php

declare(strict_types=1);

namespace App\Fleet\Service;

use App\Fleet\Exception\FleetAlreadyInTransitException;
use App\Fleet\Exception\FleetNotFoundException;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-017: Auflösen einer DOCKED-Fleet. Schiffe verlieren ihre Fleet-Zuweisung
 * (werden 'unassigned'), Fleet-Entity wird gelöscht.
 *
 * IN_TRANSIT-Fleets können NICHT aufgelöst werden — wartet auf Ankunft.
 */
readonly class DisbandFleetCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FleetRepository $fleetRepository,
    ) {
    }

    public function __invoke(FleetId $fleetId): void
    {
        $fleet = $this->fleetRepository->find($fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException($fleetId);
        }

        if ($fleet->isInTransit()) {
            throw new FleetAlreadyInTransitException($fleetId);
        }

        // Snapshot, weil detachShip die Collection während Iteration mutiert
        $shipsSnapshot = $fleet->getShips()->toArray();
        foreach ($shipsSnapshot as $ship) {
            $fleet->detachShip($ship);
        }

        $this->em->remove($fleet);
        $this->em->flush();
    }
}
