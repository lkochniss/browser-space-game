<?php

declare(strict_types=1);

namespace App\Crew\Service;

use App\Crew\Exception\CrewNotFoundException;
use App\Crew\Exception\CrewNotIdleException;
use App\Crew\Exception\ShipAlreadyHasCaptainException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-104a Captain → Schiff Assignment.
 * - Captain muss IDLE sein
 * - Schiff hat noch keinen Captain assigned
 */
readonly class AssignCrewCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CrewRepository $crewRepository,
        private ShipRepository $shipRepository,
    ) {
    }

    public function __invoke(CrewId $crewId, ShipId $shipId): Crew
    {
        $crew = $this->crewRepository->find($crewId);
        if ($crew === null) {
            throw new CrewNotFoundException($crewId);
        }
        if ($crew->getStatus() !== CrewStatus::IDLE) {
            throw new CrewNotIdleException($crew);
        }

        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipNotFoundException($shipId);
        }

        // Schiff darf nur einen Captain haben (Foundation; T-104c andere Rollen
        // brauchen eigene Mappings)
        if ($crew->getType() === CrewType::CAPTAIN) {
            $existing = $this->crewRepository->findOneBy([
                'assignedShip' => $ship,
                'type' => CrewType::CAPTAIN,
                'status' => CrewStatus::ASSIGNED,
            ]);
            if ($existing !== null && !$existing->getId()->equals($crewId)) {
                throw new ShipAlreadyHasCaptainException($shipId);
            }
        }

        $crew->assignToShip($ship);
        $this->em->flush();

        return $crew;
    }
}
