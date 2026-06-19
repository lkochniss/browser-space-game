<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Exception\NotATransportShipException;
use App\Ship\Exception\PlanetNotFoundException;
use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Exception\ShipNotReadyException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-015 Magic-Dock: setzt Transport-Schiff "instant" an Ziel-Planet.
 *
 * Out-of-Scope: Echtzeit-Movement (T-017), Treibstoff (T-066/T-105),
 * Pirat-Encounter beim Reisen (T-074).
 */
readonly class DockTransportShipCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShipRepository $shipRepository,
        private PlanetRepository $planetRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(ShipId $shipId, PlanetId $targetPlanetId): Ship
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

        $target = $this->planetRepository->find($targetPlanetId);
        if ($target === null) {
            throw new PlanetNotFoundException($targetPlanetId);
        }

        $ship->setPlanet($target);
        $this->em->flush();

        return $ship;
    }
}
