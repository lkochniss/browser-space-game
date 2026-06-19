<?php

declare(strict_types=1);

namespace App\Planet\Service;

use App\Common\Interface\ClockInterface;
use App\Planet\Exception\ColonyShipNotDockedException;
use App\Planet\Exception\NotAColonyShipException;
use App\Planet\Exception\PlanetAlreadyClaimedException;
use App\Planet\Exception\PlanetNotFoundException;
use App\Planet\Exception\ShipNotFoundException;
use App\Planet\Exception\ShipNotReadyException;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-014 Kolonisation: COLONY_SHIP wird beim Kolonisieren verbraucht (User-Decision).
 *
 * Ablauf:
 * - Schiff existiert + ist COLONY_SHIP + isReady (Wallclock fertig)
 * - Heimat-Planet (= ship.planet) existiert + hat Player
 * - Target-Planet existiert + ist NICHT geclaimt
 * - Pop-Transfer: Heimat verliert assigned-Pop (release+kill), Target erhält Start-Pop
 * - Player claimt Target-Planet
 * - Schiff wird gelöscht
 *
 * Out-of-Scope: Erkundungs-Check (T-087), Movement-Time (T-017).
 */
readonly class ColonizePlanetCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private ShipRepository $shipRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(ShipId $shipId, PlanetId $targetPlanetId): Planet
    {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipNotFoundException($shipId);
        }

        if ($ship->getType() !== ShipType::COLONY_SHIP) {
            throw new NotAColonyShipException($shipId, $ship->getType());
        }

        if (!$ship->isReady($this->clock->now())) {
            throw new ShipNotReadyException($shipId);
        }

        $homePlanet = $ship->getPlanet();
        if ($homePlanet === null) {
            throw new ColonyShipNotDockedException($shipId);
        }

        $player = $homePlanet->getPlayer();
        if ($player === null) {
            // Defensiv: Heimat-Planet hat keinen Player. In aktueller Domain wird das nie passieren
            // (Schiff wird auf Player-Planet gebaut), aber explizit mappen wir das auf den
            // gleichen Fehler wie "Schiff hat kein Heimat-Planet".
            throw new ColonyShipNotDockedException($shipId);
        }

        $targetPlanet = $this->planetRepository->find($targetPlanetId);
        if ($targetPlanet === null) {
            throw new PlanetNotFoundException($targetPlanetId);
        }

        if ($targetPlanet->getPlayer() !== null) {
            throw new PlanetAlreadyClaimedException($targetPlanetId);
        }

        $popTransfer = $ship->getPopulationAssigned();

        // Heimat: Pop verlässt den Planeten (release: assigned→free, kill: free → 0).
        // Netto: assigned -= popTransfer, total -= popTransfer.
        $homePop = $homePlanet->getPopulation();
        $homePop->release($popTransfer);
        $homePop->kill($popTransfer);

        // Target: Player claimt + Pop arrives.
        $player->claimPlanet($targetPlanet);
        $targetPlanet->getPopulation()->grow($popTransfer);

        $this->em->remove($ship);
        $this->em->flush();

        return $targetPlanet;
    }
}
