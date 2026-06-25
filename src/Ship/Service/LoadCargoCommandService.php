<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Resource\Service\ResourceVolumeConfig;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\InsufficientPopulationException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\ShipCargoOverflowException;
use App\Ship\Exception\ShipNotDockedException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-015 / T-178 Cargo-Load.
 *
 * Lädt Resources + Pop vom dockedAt-Planet ODER Station ins Schiff.
 *
 * Seit T-178 (volume-based): Jedes Schiff kann laden (kein Transport-Filter).
 * Volume-Cap-Check via `Ship::cargoVolumeCapacity` + `ResourceVolumeConfig`.
 * Pop-Mechanik: Pop wird auf Heimat assigned (analog Schiffs-Crew, T-012).
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

        if (!$ship->isReady($this->clock->now())) {
            throw new ShipNotReadyException($shipId);
        }

        $planet = $ship->getPlanet();
        $station = $ship->getStation();
        if ($planet === null && $station === null) {
            throw new ShipNotDockedException($shipId);
        }

        $requestedVolume = $this->calculateVolume($resources, $popCount);
        $freeVolume = $ship->getCargoVolumeFree();
        if ($requestedVolume > $freeVolume) {
            throw new ShipCargoOverflowException($shipId, $requestedVolume, $freeVolume);
        }

        if ($station !== null) {
            // T-015b/T-015c: Source = Station. Resources via station.storage,
            // Pop via station.populationOnStation.
            $stationStorage = $station->getStorage();
            foreach ($resources as $resourceTypeValue => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $type = ResourceType::from($resourceTypeValue);
                $available = $stationStorage->getResource($type);
                if ($available < $amount) {
                    throw new InsufficientResourcesException($type, $amount, $available);
                }
            }
            // T-015c Pop-Check: Station-Pop muss reichen
            if ($popCount > 0) {
                $stationPop = $station->getPopulationOnStation();
                if ($stationPop < $popCount) {
                    throw new InsufficientPopulationException($popCount, $stationPop);
                }
            }
            foreach ($resources as $resourceTypeValue => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $type = ResourceType::from($resourceTypeValue);
                $stationStorage->unloadResource($type, $amount);
                $ship->loadResourceCargo($type, $amount);
            }
            if ($popCount > 0) {
                $station->setPopulationOnStation($station->getPopulationOnStation() - $popCount);
                $ship->loadPopCargo($popCount);
            }
            $this->em->flush();

            return $ship;
        }

        // Source = Planet (T-015 default-Pfad)
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

    /**
     * @param array<string, int> $resources
     */
    private function calculateVolume(array $resources, int $popCount): int
    {
        $volume = 0.0;
        foreach ($resources as $resourceTypeValue => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $type = ResourceType::from($resourceTypeValue);
            $volume += $amount * ResourceVolumeConfig::getMultiForResource($type);
        }
        $volume += $popCount * ResourceVolumeConfig::getPopMulti();

        return (int) ceil($volume);
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
