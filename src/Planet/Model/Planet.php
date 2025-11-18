<?php

namespace App\Planet\Model;

use App\Building\Model\Building;
use App\Player\Model\Player;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\ValueObject\ResourceType;
use ValueObject\PlanetId;

class Planet
{
    /**
     * @param PlanetId $id
     * @param Player|null $player
     * @param iterable<Building> $buildings
     * @param iterable<Resource> $resources
     * @param iterable<ResourceDeposit> $resourceDeposits
     */
   public function __construct(private PlanetId $id, private ?Player $player, private iterable $buildings, private iterable $resources, private iterable $resourceDeposits)
   {
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

    public function setBuildings(iterable $buildings): void
    {
        $this->buildings = $buildings;
    }

    /**
     * @return iterable<Resource>
     */
    public function getResources(): iterable
    {
        return $this->resources;
    }

    public function getResource(ResourceType $resourceType): Resource
    {
        return current(array_filter(
            $this->resources,
            fn (Resource $resource) => $resource->getType() === $resourceType
        ));
    }

    public function setResources(iterable $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * @return iterable<ResourceDeposit>
     */
    public function getResourceDeposits(): iterable
    {
        return $this->resourceDeposits;
    }

    public function getResourceDeposit(ResourceType $resourceType): ResourceDeposit
    {
        return current(array_filter(
            $this->resourceDeposits,
            fn (ResourceDeposit $deposit) => $deposit->getResourceType() === $resourceType
        ));
    }

    public function setResourceDeposits(iterable $resourceDeposits): void
    {
        $this->resourceDeposits = $resourceDeposits;
    }
}
