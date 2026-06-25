<?php

declare(strict_types=1);

namespace App\Trade\Service;

use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetId;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Trade\Exception\InvalidTradeRouteException;
use App\Trade\Exception\ShipAlreadyBoundException;
use App\Trade\Model\TradeRoute;
use App\Trade\Repository\TradeRouteRepository;
use App\Trade\ValueObject\TradeRouteId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-110 Foundation-Service für CreateFixedRoute + CreateSingleTrip Commands.
 * Beide teilen sich Validation + Fleet-Auto-Setup.
 *
 * Fleet-Setup: Route bindet das Schiff an eine dedizierte single-ship Fleet
 * (analog T-017 CreateFleetCommand, aber ohne dessen Validation-Stack).
 * Cancel-Route unbindet das Ship wieder.
 */
readonly class CreateTradeRouteCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepo,
        private PlanetRepository $planetRepo,
        private ShipRepository $shipRepo,
        private TradeRouteRepository $routeRepo,
    ) {
    }

    public function createFixed(
        PlayerId $playerId,
        ShipId $shipId,
        PlanetId $sourceId,
        PlanetId $targetId,
        ResourceType $outboundResource,
        int $outboundQty,
        ?ResourceType $returnResource,
        ?int $returnQty,
    ): TradeRoute {
        [$player, $source, $target, $ship] = $this->resolveAndValidate(
            $playerId,
            $shipId,
            $sourceId,
            $targetId,
            $outboundResource,
            $outboundQty,
        );

        $route = TradeRoute::createFixed(
            id: TradeRouteId::generate(),
            owner: $player,
            source: $source,
            target: $target,
            ship: $ship,
            outboundResource: $outboundResource,
            outboundQty: $outboundQty,
            returnResource: $returnResource,
            returnQty: $returnQty,
        );

        $this->bindShipToRouteFleet($ship, $source, $player);
        $this->em->persist($route);
        $this->em->flush();

        return $route;
    }

    public function createSingleTrip(
        PlayerId $playerId,
        ShipId $shipId,
        PlanetId $sourceId,
        PlanetId $targetId,
        ResourceType $resource,
        int $qty,
    ): TradeRoute {
        [$player, $source, $target, $ship] = $this->resolveAndValidate(
            $playerId,
            $shipId,
            $sourceId,
            $targetId,
            $resource,
            $qty,
        );

        $route = TradeRoute::createSingleTrip(
            id: TradeRouteId::generate(),
            owner: $player,
            source: $source,
            target: $target,
            ship: $ship,
            resource: $resource,
            qty: $qty,
        );

        $this->bindShipToRouteFleet($ship, $source, $player);
        $this->em->persist($route);
        $this->em->flush();

        return $route;
    }

    /**
     * @return array{Player, Planet, Planet, Ship}
     */
    private function resolveAndValidate(
        PlayerId $playerId,
        ShipId $shipId,
        PlanetId $sourceId,
        PlanetId $targetId,
        ResourceType $outboundResource,
        int $outboundQty,
    ): array {
        $player = $this->playerRepo->find($playerId);
        if ($player === null) {
            throw new \DomainException(sprintf('Player %s not found', $playerId));
        }

        $source = $this->planetRepo->find($sourceId)
            ?? throw new \DomainException(sprintf('Source-Planet %s not found', $sourceId));
        $target = $this->planetRepo->find($targetId)
            ?? throw new \DomainException(sprintf('Target-Planet %s not found', $targetId));

        if ($sourceId->equals($targetId)) {
            throw InvalidTradeRouteException::sameSourceAndTarget();
        }
        if ($source->getPlayer()?->getId()->equals($playerId) !== true) {
            throw InvalidTradeRouteException::planetNotOwnedByPlayer((string) $sourceId);
        }
        if ($target->getPlayer()?->getId()->equals($playerId) !== true) {
            throw InvalidTradeRouteException::planetNotOwnedByPlayer((string) $targetId);
        }

        $ship = $this->shipRepo->find($shipId)
            ?? throw new \DomainException(sprintf('Ship %s not found', $shipId));

        if ($ship->getFleet() !== null) {
            throw new ShipAlreadyBoundException($shipId);
        }
        if ($this->routeRepo->findByShip($ship) !== null) {
            throw new ShipAlreadyBoundException($shipId);
        }
        if ($ship->getPlanet()?->getId()->equals($sourceId) !== true) {
            throw InvalidTradeRouteException::shipNotDocked();
        }

        // T-110/T-178 Cargo-Volume-Check: outboundQty × ResourceVolumeConfig-Multi
        // muss in Ship-Volume passen (m³-basiert seit T-178).
        $neededVolume = (int) ceil(
            $outboundQty * \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($outboundResource),
        );
        if ($ship->getCargoVolumeCapacity() < $neededVolume) {
            throw InvalidTradeRouteException::shipCargoTooSmall($neededVolume, $ship->getCargoVolumeCapacity());
        }

        return [$player, $source, $target, $ship];
    }

    private function bindShipToRouteFleet(Ship $ship, Planet $source, Player $player): void
    {
        $fleet = new Fleet(
            id: FleetId::generate(),
            player: $player,
            status: FleetStatus::DOCKED,
            originPlanet: $source,
        );
        $fleet->attachShip($ship);
        $this->em->persist($fleet);
    }
}
