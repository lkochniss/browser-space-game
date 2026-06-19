<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Fleet\ValueObject\FleetStatus;
use App\POI\Model\AsteroidField;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\InvalidSalvageTargetException;
use App\Ship\Exception\NotASalvageShipException;
use App\Ship\Exception\PoiNotFoundException;
use App\Ship\Exception\SalvageTargetNotInSystemException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-016 Echtzeit-Salvage starten.
 *
 * Validation:
 * - Ship existiert + isReady + Type = SALVAGE
 * - POI existiert + ist AsteroidField (DebrisField wird via T-021 nachgepflegt)
 * - AsteroidField hat noch was vom angefragten ResourceType
 * - Ship ist im selben SolarSystem wie POI:
 *   - Ship in Fleet DOCKED → fleet.originPlanet.solarSystem == poi.solarSystem
 *   - Ship NICHT in Fleet aber docked → ship.planet.solarSystem == poi.solarSystem
 *   - Ship in Fleet IN_TRANSIT → reject
 *   - Ship undocked + ohne Fleet → reject
 *
 * Effekt: Schiff bekommt salvageTargetPoiId + salvageResourceType +
 * salvageLastTickAt = now. SalvageProcessor (Tick) macht den eigentlichen
 * Extract.
 *
 * Idempotent: Wenn Schiff bereits den gleichen Target salvaget → no-op.
 */
readonly class StartSalvageCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShipRepository $shipRepository,
        private PoiRepository $poiRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(ShipId $shipId, PoiId $poiId, ResourceType $resourceType): Ship
    {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipNotFoundException($shipId);
        }

        if (!$ship->getType()->isSalvage()) {
            throw new NotASalvageShipException($shipId, $ship->getType());
        }

        $now = $this->clock->now();

        if (!$ship->isReady($now)) {
            throw new ShipNotReadyException($shipId);
        }

        $poi = $this->poiRepository->find($poiId);
        if ($poi === null) {
            throw new PoiNotFoundException($poiId);
        }

        if (!$poi instanceof AsteroidField) {
            throw new InvalidSalvageTargetException(
                $poiId,
                'only AsteroidField targets supported in T-016 (DebrisField via T-021)',
            );
        }

        if ($poi->getAmount($resourceType) <= 0) {
            throw new InvalidSalvageTargetException(
                $poiId,
                sprintf('AsteroidField has no %s available', $resourceType->value),
            );
        }

        $shipSystemId = $this->resolveShipSystemId($ship);
        if ($shipSystemId === null) {
            throw new SalvageTargetNotInSystemException($shipId);
        }

        $poiSystemId = $poi->getSolarSystem()->getId();
        if (!$shipSystemId->equals($poiSystemId)) {
            throw new SalvageTargetNotInSystemException($shipId);
        }

        $ship->startSalvage($poiId->__toString(), $resourceType, $now);
        $this->em->flush();

        return $ship;
    }

    private function resolveShipSystemId(Ship $ship): ?\App\SolarSystem\ValueObject\SolarSystemId
    {
        $fleet = $ship->getFleet();
        if ($fleet !== null) {
            if ($fleet->getStatus() === FleetStatus::IN_TRANSIT) {
                return null;
            }
            $origin = $fleet->getOriginPlanet();
            if ($origin === null) {
                return null;
            }
            $sys = $origin->getSolarSystem();

            return $sys?->getId();
        }

        $planet = $ship->getPlanet();
        if ($planet === null) {
            return null;
        }
        $sys = $planet->getSolarSystem();

        return $sys?->getId();
    }
}
