<?php

declare(strict_types=1);

namespace App\Discovery\Service;

use App\Common\Interface\ClockInterface;
use App\Common\Service\Randomizer;
use App\Discovery\Model\PlayerSystemDiscovery;
use App\Discovery\Repository\PlayerSystemDiscoveryRepository;
use App\Player\Model\Player;
use App\SolarSystem\Repository\SolarSystemRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-018 Teleskop-Discovery (globaler Service, nicht TickProcessor).
 *
 * Pro Tick:
 * - Summiert Telescope-Level über alle Planets des Players
 * - Wählt N=Total-Level zufällige unbekannte SolarSystems aus
 * - Persistiert PlayerSystemDiscovery-Einträge
 *
 * Initial-Discovery (Heimat-System) wird bei ClaimStartPlanet via separater
 * markDiscovered()-Methode gesetzt.
 */
readonly class TelescopeDiscoveryService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SolarSystemRepository $solarSystemRepository,
        private PlayerSystemDiscoveryRepository $discoveryRepository,
        private ClockInterface $clock,
        private Randomizer $randomizer,
    ) {
    }

    /**
     * Markiert ein einzelnes System als entdeckt für den Player. No-op wenn schon
     * entdeckt. Genutzt von ClaimStartPlanet für Heimat-System.
     */
    public function markDiscovered(Player $player, \App\SolarSystem\Model\SolarSystem $system): void
    {
        if ($this->discoveryRepository->isDiscovered($player, $system)) {
            return;
        }
        $entry = PlayerSystemDiscovery::generate($player, $system, $this->clock->now());
        $this->em->persist($entry);
    }

    /**
     * @return int Anzahl neu entdeckter Systeme
     */
    public function runTickForPlayer(Player $player): int
    {
        $totalLevel = 0;
        $now = $this->clock->now();
        foreach ($player->getPlanets() as $planet) {
            $totalLevel += $planet->getTelescopeLevel($now);
        }
        if ($totalLevel === 0) {
            return 0;
        }

        $allSystems = $this->solarSystemRepository->findAll();
        $known = $this->discoveryRepository->findByPlayer($player);
        $knownIds = [];
        foreach ($known as $d) {
            $knownIds[$d->getSolarSystem()->getId()->__toString()] = true;
        }

        $unknown = [];
        foreach ($allSystems as $sys) {
            if (!isset($knownIds[$sys->getId()->__toString()])) {
                $unknown[] = $sys;
            }
        }
        if ($unknown === []) {
            return 0;
        }

        $reveal = min($totalLevel, count($unknown));
        // Random-pick: shuffle via Randomizer-driven Fisher-Yates auf Indexes
        $indexes = range(0, count($unknown) - 1);
        for ($i = count($indexes) - 1; $i > 0; $i--) {
            $j = $this->randomizer->intBetween(0, $i);
            [$indexes[$i], $indexes[$j]] = [$indexes[$j], $indexes[$i]];
        }

        for ($i = 0; $i < $reveal; $i++) {
            $entry = PlayerSystemDiscovery::generate($player, $unknown[$indexes[$i]], $now);
            $this->em->persist($entry);
        }
        $this->em->flush();

        return $reveal;
    }
}
