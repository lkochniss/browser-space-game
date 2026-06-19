<?php

declare(strict_types=1);

namespace App\Fleet\Service;

use App\Common\Interface\ClockInterface;
use App\Fleet\Exception\EmptyFleetException;
use App\Fleet\Exception\InvalidFleetCompositionException;
use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetId;
use App\Fleet\ValueObject\FleetStatus;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-017: Erstellt eine Fleet aus 1+ Schiffen am gleichen Planet.
 *
 * Validation:
 * - mind. 1 Ship
 * - alle Ships gehören dem Player (Heimat-Planet.player == Fleet-Owner)
 * - alle Ships docked (Ship.planet != null)
 * - alle Ships am gleichen Planet (originPlanet)
 * - alle Ships isReady (Build fertig)
 * - keines bereits in einer Fleet
 */
readonly class CreateFleetCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepository,
        private ShipRepository $shipRepository,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param list<ShipId> $shipIds
     */
    public function __invoke(PlayerId $playerId, array $shipIds): Fleet
    {
        if (count($shipIds) === 0) {
            throw new EmptyFleetException();
        }

        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new InvalidFleetCompositionException(sprintf(
                'Player "%s" not found',
                $playerId,
            ));
        }

        $now = $this->clock->now();
        $ships = [];
        $originPlanet = null;

        foreach ($shipIds as $shipId) {
            $ship = $this->shipRepository->find($shipId);
            if ($ship === null) {
                throw new InvalidFleetCompositionException(sprintf(
                    'Ship "%s" not found',
                    $shipId,
                ));
            }
            if (!$ship->isReady($now)) {
                throw new InvalidFleetCompositionException(sprintf(
                    'Ship "%s" is not ready (still under construction)',
                    $shipId,
                ));
            }
            if ($ship->getFleet() !== null) {
                throw new InvalidFleetCompositionException(sprintf(
                    'Ship "%s" is already in a fleet',
                    $shipId,
                ));
            }
            $shipPlanet = $ship->getPlanet();
            if ($shipPlanet === null) {
                throw new InvalidFleetCompositionException(sprintf(
                    'Ship "%s" is not docked at any planet',
                    $shipId,
                ));
            }
            if ($shipPlanet->getPlayer() === null
                || !$shipPlanet->getPlayer()->getId()->equals($player->getId())
            ) {
                throw new InvalidFleetCompositionException(sprintf(
                    'Ship "%s" is not on a planet owned by the fleet owner',
                    $shipId,
                ));
            }
            if ($originPlanet === null) {
                $originPlanet = $shipPlanet;
            } elseif (!$originPlanet->getId()->equals($shipPlanet->getId())) {
                throw new InvalidFleetCompositionException(
                    'All ships must be docked at the same planet',
                );
            }

            $ships[] = $ship;
        }

        $fleet = new Fleet(
            id: FleetId::generate(),
            player: $player,
            status: FleetStatus::DOCKED,
            originPlanet: $originPlanet,
        );

        foreach ($ships as $ship) {
            $fleet->attachShip($ship);
        }

        $this->em->persist($fleet);
        $this->em->flush();

        return $fleet;
    }
}
