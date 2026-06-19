<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\InsufficientPopulationException;
use App\Ship\Exception\InsufficientResourcesException;
use App\Ship\Exception\MissingShipyardException;
use App\Ship\Exception\PlanetNotFoundException;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-012 Build-Service. Mit T-014 refactored: Cost+Duration+Pop pro ShipType
 * via ShipCostConfig.
 *
 * Voraussetzung: Planet hat fertige SHIPYARD (T-011) auf Level >= 1.
 * Wallclock-Bauzeit analog T-062.
 */
readonly class BuildShipCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private ShipCostConfig $costConfig,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlanetId $planetId, ShipType $type = ShipType::GENERIC): Ship
    {
        $planet = $this->planetRepository->find($planetId);
        if ($planet === null) {
            throw new PlanetNotFoundException($planetId);
        }

        $now = $this->clock->now();

        if (!$planet->hasShipyard($now)) {
            throw new MissingShipyardException($planetId);
        }

        $resourceCost = $this->costConfig->getResourceCost($type);
        $popCost = $this->costConfig->getPopulationCost($type);

        $this->checkResources($planet, $resourceCost);
        $this->checkPopulation($planet, $popCost);

        $this->debitResources($planet, $resourceCost);
        $planet->getPopulation()->assign($popCost);

        $ship = new Ship(
            id: ShipId::generate(),
            type: $type,
            populationAssigned: $popCost,
            cargoCapacity: $this->costConfig->getCargoCapacity($type),
        );
        $ship->setPlanet($planet);

        $duration = $this->costConfig->getDurationSeconds($type);
        $ship->setFinishedAt($now->add(new DateInterval(sprintf('PT%dS', $duration))));

        $this->em->persist($ship);
        $this->em->flush();

        return $ship;
    }

    /**
     * @param array<string,int> $cost
     */
    private function checkResources(Planet $planet, array $cost): void
    {
        foreach ($cost as $resourceTypeValue => $required) {
            $resourceType = ResourceType::from($resourceTypeValue);
            $available = $this->getResourceAmount($planet, $resourceType);
            if ($available < $required) {
                throw new InsufficientResourcesException($resourceType, $required, $available);
            }
        }
    }

    private function checkPopulation(Planet $planet, int $required): void
    {
        $free = $planet->getPopulation()->getFree();
        if ($free < $required) {
            throw new InsufficientPopulationException($required, $free);
        }
    }

    /**
     * @param array<string,int> $cost
     */
    private function debitResources(Planet $planet, array $cost): void
    {
        foreach ($cost as $resourceTypeValue => $amount) {
            $resourceType = ResourceType::from($resourceTypeValue);
            $resource = $planet->getResource($resourceType);
            $resource->setAmount($resource->getAmount() - $amount);
        }
    }

    private function getResourceAmount(Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource->getAmount();
            }
        }

        return 0;
    }
}
