<?php

namespace App\Planet\Model;

use App\Building\Model\Building;
use App\Building\Model\BuildingCollection;
use App\Player\Model\Player;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceCollection;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Model\ResourceDepositCollection;
use App\Resource\ValueObject\ResourceType;
use ValueObject\PlanetId;

class Planet
{
    public function __construct(
        private PlanetId                  $id,
        private ?Player                   $player,
        private BuildingCollection        $buildings,
        private ResourceCollection        $resources,
        private ResourceDepositCollection $resourceDeposits
    )
    {
    }

    public static function generatePlanet(): self
    {
        return new self(
            PlanetId::generate(),
            null,
            new BuildingCollection(),
            new ResourceCollection(),
            new ResourceDepositCollection(),
        );
    }

    public static function claimPlanet(
        PlanetId $planetId,
        Player $player,
        BuildingCollection $buildings,
        ResourceCollection $resources,
        ResourceDepositCollection $resourceDeposits
    ): self{
        return new self(
            $planetId,
            $player,
            $buildings,
            $resources,
            $resourceDeposits
        );
    }

    public function getId(): PlanetId
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return iterable<Building>
     */
    public function getBuildings(): iterable
    {
        return $this->buildings;
    }

    public function setBuildings(BuildingCollection $buildings): void
    {
        $this->buildings = $buildings;
    }

    public function getResources(): ResourceCollection
    {
        return $this->resources;
    }

    public function getResource(ResourceType $resourceType): Resource
    {
        return current(array_filter(
            $this->resources->toArray(),
            fn(Resource $resource) => $resource->getType() === $resourceType
        ));
    }

    public function setResources(ResourceCollection $resources): void
    {
        $this->resources = $resources;
    }

    public function getResourceDeposits(): ResourceDepositCollection
    {
        return $this->resourceDeposits;
    }

    public function getResourceDeposit(ResourceType $resourceType): ResourceDeposit
    {
        return current(array_filter(
            $this->resourceDeposits->toArray(),
            fn(ResourceDeposit $deposit) => $deposit->getResourceType() === $resourceType
        ));
    }

    public function setResourceDeposits(ResourceDepositCollection $resourceDeposits): void
    {
        $this->resourceDeposits = $resourceDeposits;
    }
}
