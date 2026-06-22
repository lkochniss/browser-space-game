<?php

declare(strict_types=1);

namespace App\Fleet\Service;

use App\POI\Model\Wormhole;
use App\POI\Repository\PoiRepository;
use App\SolarSystem\Model\SolarSystem;

/**
 * T-017b: Findet Wormhole-Pair-Routen zwischen zwei Solar-Systemen.
 *
 * Algorithmus: liste alle Wormholes im Origin-System; prüfe für jedes ob sein
 * Twin im Target-System steht. Erstes Match wird returned (mehrere Pairs sind
 * theoretisch möglich, aber selten).
 *
 * Returns das Wormhole-POI im Origin-System (Twin lebt im Target). Caller
 * nutzt `getRequiredTechSlug()` für Tech-Check.
 */
readonly class WormholeRouteDetector
{
    public function __construct(
        private PoiRepository $poiRepository,
    ) {
    }

    public function findRoute(SolarSystem $origin, SolarSystem $target): ?Wormhole
    {
        if ($origin->getId()->equals($target->getId())) {
            return null; // Same system, kein Wormhole nötig
        }

        $pois = $this->poiRepository->findBySolarSystem($origin);
        foreach ($pois as $poi) {
            if (!$poi instanceof Wormhole) {
                continue;
            }
            $twin = $poi->getTwin();
            if ($twin === null) {
                continue;
            }
            if ($twin->getSolarSystem()->getId()->equals($target->getId())) {
                return $poi;
            }
        }

        return null;
    }
}
