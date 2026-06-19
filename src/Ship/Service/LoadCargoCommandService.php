<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\CargoCapacityExceededException;
use App\Ship\Exception\InsufficientPopulationException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\NotATransportShipException;
use App\Ship\Exception\ShipNotDockedException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-015 Cargo-Load.
 *
 * Lädt Resources + Pop vom dockedAt-Planet ins Schiff. Hard-Reject bei
 * Capacity-Überschreitung (User-Decision) und fehlenden Resources/Pop.
 *
 * Pop-Mechanik: Pop wird auf Heimat assigned (analog Schiffs-Crew, T-012).
 * Bei Unload landet die Pop am Ziel-Planet.
 */
readonly class LoadCargoCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShipRepository $shipRepository,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param array<string, int> $resources
     */
    public function __invoke(ShipId $shipId, array $resources, int $popCount): Ship
    {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipNotFoundException($shipId);
        }

        if (!$ship->getType()->isTransport()) {
            throw new NotATransportShipException($shipId, $ship->getType());
        }

        if (!$ship->isReady($this->clock->now())) {
            throw new ShipNotReadyException($shipId);
        }

        $planet = $ship->getPlanet();
        if ($planet === null) {
            throw new ShipNotDockedException($shipId);
        }

        $totalUnits = array_sum($resources) + $popCount;
        if ($totalUnits > $ship->getCargoFreeUnits()) {
            throw new CargoCapacityExceededException($shipId, $totalUnits, $ship->getCargoFreeUnits());
        }

        // Resource-Verfügbarkeit auf Planet validieren
        foreach ($resources as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $type = ResourceType::from($resourceTypeValue);
            $available = $this->getResourceAmount($planet, $type);
            if ($available < $amount) {
                throw new InsufficientResourcesException($type, $amount, $available);
            }
        }

        // Pop-Verfügbarkeit
        if ($popCount > 0) {
            $free = $planet->getPopulation()->getFree();
            if ($free < $popCount) {
                throw new InsufficientPopulationException($popCount, $free);
            }
        }

        // Mutationen
        foreach ($resources as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $type = ResourceType::from($resourceTypeValue);
            $resource = $planet->getResource($type);
            $resource->setAmount($resource->getAmount() - $amount);

            $ship->loadResourceCargo($type, $amount);
        }

        if ($popCount > 0) {
            $planet->getPopulation()->assign($popCount);
            $ship->loadPopCargo($popCount);
        }

        $this->em->flush();

        return $ship;
    }

    private function getResourceAmount(\App\Planet\Model\Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource->getAmount();
            }
        }

        return 0;
    }
}
