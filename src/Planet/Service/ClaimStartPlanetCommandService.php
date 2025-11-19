<?php

namespace App\Planet\Service;

use App\Building\Model\Building;
use App\Building\Model\BuildingCollection;
use App\Planet\Model\Planet;
use App\Planet\Model\PlanetCollection;
use App\Player\Model\Player;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceCollection;
use App\Resource\Model\ResourceDeposit;
use App\Resource\Model\ResourceDepositCollection;
use App\Resource\ValueObject\BuildingType;
use App\Resource\ValueObject\ResourceType;
use ValueObject\PlanetId;
use ValueObject\PlayerId;

class ClaimStartPlanetCommandService
{
    public function __invoke(PlayerId $playerId, PlanetId $planetId): Player
    {
        $player = new Player($playerId, new PlanetCollection());
        $planet = Planet::claimPlanet(
            $planetId,
            $player,
            $this->generateStartBuildings(),
            $this->generateStartResources(),
            $this->generateStartResourceDeposits()
        );

        $player->claimPlanet($planet);

        return $player;
    }

    private function generateStartBuildings(): iterable
    {
        return new BuildingCollection([
            Building::createNewBuilding(BuildingType::IRON_MINE)
        ]);
    }

    private function generateStartResources(): iterable
    {
        return new ResourceCollection([
            Resource::generateEmptyResource(ResourceType::IRON_ORE),
        ]);
    }

    private function generateStartResourceDeposits(): iterable
    {
        return new ResourceDepositCollection([
            ResourceDeposit::generateDepositWithAmount(ResourceType::IRON_ORE, 1000),
        ]);
    }
}
