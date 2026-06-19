<?php

declare(strict_types=1);

namespace App\POI\Service;

use App\Building\ValueObject\BuildingType;
use App\Common\Interface\ClockInterface;
use App\POI\Exception\InsufficientPopulationException;
use App\POI\Exception\InsufficientResourcesException;
use App\POI\Exception\MissingShipyardInSystemException;
use App\POI\Exception\PlayerNotFoundException;
use App\POI\Exception\SolarSystemNotFoundException;
use App\POI\Exception\StationAlreadyExistsInSystemException;
use App\POI\Model\Poi;
use App\POI\Model\SpaceStation;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Repository\SolarSystemRepository;
use App\SolarSystem\ValueObject\SolarSystemId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-023 Build-Service. Foundation:
 * - Player muss in Ziel-System einen eigenen Planet mit Shipyard >= L3 haben
 * - Max 1 SpaceStation pro System
 * - Cost: 5000 Iron-Bar + 1000 Aluminum-Ore + 200 Titanium-Ore + 200 Pop
 *   (Initial-Pop-on-Station, vom Heimat-Planet entzogen)
 * - Wallclock-Build kommt mit Folge-Ticket — Foundation: Station ist sofort
 *   ACTIVE (analog T-012-Pattern wäre Wallclock-Build möglich, aber komplex
 *   weil POIs noch keine isReady-Mechanik haben)
 *
 * Out-of-Scope: Maintenance-Tick, Übernahme via ABANDONED, Wallclock-Build,
 * Resource-Lager via T-015 LoadCargo.
 */
readonly class BuildSpaceStationCommandService
{
    public const REQUIRED_SHIPYARD_LEVEL = 3;
    public const INITIAL_POPULATION_ON_STATION = 200;
    /** @var array<string, int> */
    public const RESOURCE_COSTS = [
        // hardcoded für Foundation; Folge-Ticket kann pro StationType variieren
    ];

    private const COST_IRON_BAR = 5000;
    private const COST_ALUMINUM_ORE = 1000;
    private const COST_TITANIUM_ORE = 200;
    private const POP_COST_FROM_HOME = 200;

    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepository,
        private SolarSystemRepository $solarSystemRepository,
        private PoiRepository $poiRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlayerId $playerId, SolarSystemId $systemId): SpaceStation
    {
        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new PlayerNotFoundException($playerId);
        }

        $system = $this->solarSystemRepository->find($systemId);
        if ($system === null) {
            throw new SolarSystemNotFoundException($systemId);
        }

        // Max 1 Station pro System
        foreach ($this->poiRepository->findBySolarSystem($system) as $existing) {
            if ($existing instanceof SpaceStation) {
                throw new StationAlreadyExistsInSystemException($systemId);
            }
        }

        // Player braucht Shipyard L3+ auf einem Planet im Ziel-System
        $now = $this->clock->now();
        $shipyardPlanet = $this->findEligibleShipyardPlanet($player, $system, $now);
        if ($shipyardPlanet === null) {
            throw new MissingShipyardInSystemException($systemId, self::REQUIRED_SHIPYARD_LEVEL);
        }

        $this->checkAndDebitResources($shipyardPlanet);
        $this->checkAndAssignPopulation($shipyardPlanet);

        $station = new SpaceStation(
            id: PoiId::generate(),
            solarSystem: $system,
            owner: $player,
            name: sprintf('%s Station', $system->getName()),
            populationOnStation: self::INITIAL_POPULATION_ON_STATION,
            storageCapacity: SpaceStation::DEFAULT_STORAGE_CAPACITY,
        );
        $system->addPoi($station);

        $this->em->persist($station);
        $this->em->flush();

        return $station;
    }

    private function findEligibleShipyardPlanet(
        \App\Player\Model\Player $player,
        \App\SolarSystem\Model\SolarSystem $system,
        ?\DateTimeImmutable $now,
    ): ?Planet {
        foreach ($player->getPlanets() as $planet) {
            $sys = $planet->getSolarSystem();
            if ($sys === null || !$sys->getId()->equals($system->getId())) {
                continue;
            }
            if ($planet->getShipyardLevel($now) >= self::REQUIRED_SHIPYARD_LEVEL) {
                return $planet;
            }
        }

        return null;
    }

    private function checkAndDebitResources(Planet $planet): void
    {
        $costs = [
            ResourceType::IRON_BAR->value => self::COST_IRON_BAR,
            ResourceType::ALUMINUM_ORE->value => self::COST_ALUMINUM_ORE,
            ResourceType::TITANIUM_ORE->value => self::COST_TITANIUM_ORE,
        ];

        foreach ($costs as $resourceValue => $required) {
            $type = ResourceType::from($resourceValue);
            $available = $this->getResourceAmount($planet, $type);
            if ($available < $required) {
                throw new InsufficientResourcesException($type, $required, $available);
            }
        }

        foreach ($costs as $resourceValue => $amount) {
            $resource = $planet->getResource(ResourceType::from($resourceValue));
            $resource->setAmount($resource->getAmount() - $amount);
        }
    }

    private function checkAndAssignPopulation(Planet $planet): void
    {
        $free = $planet->getPopulation()->getFree();
        if ($free < self::POP_COST_FROM_HOME) {
            throw new InsufficientPopulationException(self::POP_COST_FROM_HOME, $free);
        }
        // Pop "zieht" zur Station: kill auf Heimat-Pop (free first), Station
        // bekommt eigene populationOnStation gesetzt.
        $planet->getPopulation()->kill(self::POP_COST_FROM_HOME);
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
