<?php

declare(strict_types=1);

namespace App\Demo\Service;

use App\Common\Interface\ClockInterface;
use App\Discovery\Repository\PlayerSystemDiscoveryRepository;
use App\Fleet\Repository\FleetRepository;
use App\Player\Model\Player;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use App\Ship\Repository\ShipRepository;

/**
 * T-082d Vollständiger State-Snapshot eines Players für Action-Log.
 *
 * Format: array, JSON-serialisierbar. Enthält alle IDs + Timestamps damit
 * Wallclock-Mechaniken (finished_at, arrived_at) zur KI-Analyse erhalten bleiben.
 */
readonly class StateSnapshotter
{
    public function __construct(
        private ShipRepository $shipRepository,
        private FleetRepository $fleetRepository,
        private PlayerSystemDiscoveryRepository $discoveryRepository,
        private PlayerResearchRepository $playerResearchRepository,
        private ActiveResearchRepository $activeResearchRepository,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Player $player): array
    {
        $now = $this->clock->now();

        $planets = [];
        foreach ($player->getPlanets() as $planet) {
            $resources = [];
            foreach ($planet->getResources() as $r) {
                $resources[$r->getType()->value] = $r->getAmount();
            }

            $buildings = [];
            foreach ($planet->getBuildings() as $b) {
                $buildings[] = [
                    'id' => (string) $b->getId(),
                    'type' => $b->getType()->value,
                    'level' => $b->getLevel(),
                    'finished_at' => $b->getFinishedAt()?->format(\DateTimeInterface::ATOM),
                    'ready' => $b->isReady($now),
                ];
            }

            $deposits = [];
            foreach ($planet->getResourceDeposits() as $d) {
                $deposits[] = [
                    'type' => $d->getResourceType()->value,
                    'amount' => $d->getAmount(),
                ];
            }

            $pop = $planet->getPopulation();
            $planets[] = [
                'id' => (string) $planet->getId(),
                'type' => $planet->getType()->value,
                'size' => $planet->getSize()->value,
                'system' => $planet->getSolarSystem()?->getName(),
                'system_id' => (string) ($planet->getSolarSystem()?->getId() ?? ''),
                'resources' => $resources,
                'pop' => [
                    'total' => $pop->getTotal(),
                    'assigned' => $pop->getAssigned(),
                    'cap' => $pop->getCap(),
                ],
                'buildings' => $buildings,
                'deposits' => $deposits,
            ];
        }

        $ships = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                $cargo = [];
                foreach ($ship->getCargo()->getResources() as $resVal => $amount) {
                    $cargo[$resVal] = $amount;
                }
                $ships[] = [
                    'id' => (string) $ship->getId(),
                    'type' => $ship->getType()->value,
                    'planet_id' => (string) ($ship->getPlanet()?->getId() ?? ''),
                    'fleet_id' => (string) ($ship->getFleet()?->getId() ?? ''),
                    'finished_at' => $ship->getFinishedAt()?->format(\DateTimeInterface::ATOM),
                    'ready' => $ship->isReady($now),
                    'pop_assigned' => $ship->getPopulationAssigned(),
                    'cargo' => $cargo,
                    'cargo_pop' => $ship->getCargo()->getPopCount(),
                    'cargo_volume_capacity' => $ship->getCargoVolumeCapacity(),
                    'cargo_volume_used' => $ship->getCargoVolumeUsed(),
                    'supplies' => [
                        'water' => $ship->getSupplyWater(),
                        'food' => $ship->getSupplyFood(),
                        'oxygen' => $ship->getSupplyOxygen(),
                    ],
                    'salvaging' => $ship->isSalvaging(),
                    'salvage_target' => $ship->getSalvageTargetPoiId(),
                    'salvage_resource' => $ship->getSalvageResourceType()?->value,
                    'salvage_last_tick' => $ship->getSalvageLastTickAt()?->format(\DateTimeInterface::ATOM),
                ];
            }
        }

        $fleets = [];
        foreach ($this->fleetRepository->findAll() as $f) {
            if (!$f->getPlayer()->getId()->equals($player->getId())) {
                continue;
            }
            $shipIds = [];
            foreach ($f->getShips() as $s) {
                $shipIds[] = (string) $s->getId();
            }
            $fleets[] = [
                'id' => (string) $f->getId(),
                'status' => $f->getStatus()->value,
                'origin_planet' => (string) ($f->getOriginPlanet()?->getId() ?? ''),
                'arrived_at' => $f->getArrivedAt()?->format(\DateTimeInterface::ATOM),
                'ship_ids' => $shipIds,
            ];
        }

        $discoveries = $this->discoveryRepository->findByPlayer($player);
        $discoveredIds = [];
        foreach ($discoveries as $d) {
            $discoveredIds[] = (string) $d->getSolarSystem()->getId();
        }

        $researchLevels = [];
        foreach ($this->playerResearchRepository->findByPlayer($player) as $r) {
            $researchLevels[$r->getNodeSlug()] = $r->getLevel();
        }
        $activeResearch = null;
        $active = $this->activeResearchRepository->findActiveForPlayer($player);
        if ($active !== null) {
            $activeResearch = [
                'node_slug' => $active->getNodeSlug(),
                'target_level' => $active->getTargetLevel(),
                'started_at' => $active->getStartedAt()->format(\DateTimeInterface::ATOM),
                'finished_at' => $active->getFinishedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return [
            'clock_now' => $now->format(\DateTimeInterface::ATOM),
            'player_id' => (string) $player->getId(),
            'planets' => $planets,
            'ships' => $ships,
            'fleets' => $fleets,
            'discovered_system_ids' => $discoveredIds,
            'research_levels' => $researchLevels,
            'active_research' => $activeResearch,
        ];
    }
}
