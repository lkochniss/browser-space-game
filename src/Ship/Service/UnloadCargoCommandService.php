<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\InsufficientCargoException;
use App\Ship\Exception\NotATransportShipException;
use App\Ship\Exception\ShipNotDockedException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-015 Cargo-Unload.
 *
 * Lädt Resources + Pop vom Schiff am dockedAt-Planet aus. Hard-Reject wenn
 * Cargo-Inhalt nicht reicht. Resources werden direkt addiert (Storage-Cap-Check
 * Out-of-Scope für Foundation — würde T-061-Storage-System ähnliche Logik brauchen).
 */
readonly class UnloadCargoCommandService
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
        $station = $ship->getStation();
        if ($planet === null && $station === null) {
            throw new ShipNotDockedException($shipId);
        }

        // Validierung: genug Cargo vorhanden
        $cargo = $ship->getCargo();
        foreach ($resources as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $type = ResourceType::from($resourceTypeValue);
            $inCargo = $cargo->getResource($type);
            if ($inCargo < $amount) {
                throw new InsufficientCargoException($shipId, $type->value, $amount, $inCargo);
            }
        }
        if ($popCount > 0 && $cargo->getPopCount() < $popCount) {
            throw new InsufficientCargoException($shipId, 'pop', $popCount, $cargo->getPopCount());
        }

        if ($station !== null) {
            // T-015b/T-015c: Target = Station. Resources in station.storage,
            // Pop in station.populationOnStation (Cap-Check = T-023b Folge).
            $stationStorage = $station->getStorage();
            $needed = array_sum($resources);
            if ($needed > $station->getStorageFreeUnits()) {
                throw new \App\Ship\Exception\CargoCapacityExceededException(
                    $shipId,
                    $needed,
                    $station->getStorageFreeUnits(),
                );
            }
            foreach ($resources as $resourceTypeValue => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $type = ResourceType::from($resourceTypeValue);
                $stationStorage->loadResource($type, $amount);
                $ship->unloadResourceCargo($type, $amount);
            }
            if ($popCount > 0) {
                $station->setPopulationOnStation($station->getPopulationOnStation() + $popCount);
                $ship->unloadPopCargo($popCount);
            }
            $this->em->flush();

            return $ship;
        }

        // Target = Planet (T-015 default-Pfad)
        // Mutationen
        foreach ($resources as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $type = ResourceType::from($resourceTypeValue);
            $resource = $this->ensurePlanetResource($planet, $type);
            $resource->setAmount($resource->getAmount() + $amount);

            $ship->unloadResourceCargo($type, $amount);
        }

        if ($popCount > 0) {
            // Pop arrives als "free" Pop — wächst auf den Planeten via grow (clamped at cap).
            $planet->getPopulation()->grow($popCount);
            $ship->unloadPopCargo($popCount);
        }

        $this->em->flush();

        return $ship;
    }

    private function ensurePlanetResource(\App\Planet\Model\Planet $planet, ResourceType $type): Resource
    {
        return $planet->ensureResource($type);
    }
}
