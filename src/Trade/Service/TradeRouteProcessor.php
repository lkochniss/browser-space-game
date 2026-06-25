<?php

declare(strict_types=1);

namespace App\Trade\Service;

use App\Common\Interface\ClockInterface;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Service\MoveFleetCommandService;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Model\Planet;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Trade\Model\TradeRoute;
use App\Trade\Repository\TradeRouteRepository;
use App\Trade\ValueObject\TradeRouteLeg;
use App\Trade\ValueObject\TradeRouteStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-110 globaler Tick-Service (analog FleetArrivalService).
 *
 * Pro ACTIVE/SINGLE_TRIP-Route:
 *   - AT_SOURCE        → load outbound + dispatch MoveFleet → GOING_TO_TARGET
 *   - GOING_TO_TARGET  → wait for FleetArrival (Fleet=DOCKED at target)
 *                        → AT_TARGET
 *   - AT_TARGET        → unload outbound, +tripCounter
 *                        Single-Trip → CANCEL Route.
 *                        Fixed mit return → load return + Move → GOING_TO_SOURCE.
 *                        Fixed ohne return → Move empty zurück → GOING_TO_SOURCE.
 *   - GOING_TO_SOURCE  → wait for arrival → unload return → AT_SOURCE → Loop.
 *
 * Stops gracefully wenn Source-Resource leer ist (bleibt in AT_SOURCE) oder
 * Target-Volume voll ist (bleibt in AT_TARGET nach Unload).
 */
readonly class TradeRouteProcessor
{
    public function __construct(
        private EntityManagerInterface $em,
        private TradeRouteRepository $repo,
        private MoveFleetCommandService $moveFleet,
        private ClockInterface $clock,
    ) {
    }

    public function runTick(): int
    {
        $now = $this->clock->now();
        $processed = 0;

        foreach ($this->repo->findActive() as $route) {
            if (!$route->getStatus()->isRunning()) {
                continue;
            }
            try {
                // T-110: pro Route Loop bis State stabil. Erlaubt z.B. nach
                // Arrival (GOING_TO_TARGET → AT_TARGET) sofort Unload + Move
                // im selben Tick. Hard-Cap 5 iterations gegen Endlosschleifen.
                for ($i = 0; $i < 5; $i++) {
                    $before = $route->getCurrentLeg();
                    $beforeStatus = $route->getStatus();
                    $this->advance($route, $now);
                    if (!$route->getStatus()->isRunning()) {
                        break;
                    }
                    if ($route->getCurrentLeg() === $before && $route->getStatus() === $beforeStatus) {
                        break;
                    }
                }
                ++$processed;
            } catch (\Throwable $e) {
                // T-110 Foundation: Routes pausieren bei Fehler statt durchzubrechen.
                // T-110b kann Granular-Refill-Logic + Error-Hooks ergänzen.
                $route->pause();
            }
        }

        $this->em->flush();

        return $processed;
    }

    private function advance(TradeRoute $route, \DateTimeImmutable $now): void
    {
        $ship = $route->getBoundShip();
        $fleet = $ship->getFleet();
        if ($fleet === null) {
            // Defensiv: Fleet wurde extern entfernt → Route hat keine Ausführung mehr.
            $route->cancel();

            return;
        }

        switch ($route->getCurrentLeg()) {
            case TradeRouteLeg::AT_SOURCE:
                if ($fleet->getStatus() !== FleetStatus::DOCKED) {
                    return;
                }
                if ($ship->getPlanet()?->getId()->equals($route->getSourcePlanet()->getId()) !== true) {
                    return;
                }
                $loaded = $this->loadOnto(
                    $route->getSourcePlanet(),
                    $ship,
                    $route->getOutboundResource(),
                    $route->getOutboundQty(),
                );
                if ($loaded <= 0) {
                    return; // Source-Resource leer → graceful wait
                }
                $this->moveFleet->__invoke($fleet->getId(), $route->getTargetPlanet()->getId());
                $route->setLeg(TradeRouteLeg::GOING_TO_TARGET);

                return;

            case TradeRouteLeg::GOING_TO_TARGET:
                if ($fleet->getStatus() === FleetStatus::DOCKED
                    && $ship->getPlanet()?->getId()->equals($route->getTargetPlanet()->getId()) === true
                ) {
                    $route->setLeg(TradeRouteLeg::AT_TARGET);
                }

                return;

            case TradeRouteLeg::AT_TARGET:
                $this->unloadInto(
                    $route->getTargetPlanet(),
                    $ship,
                    $route->getOutboundResource(),
                );
                $route->recordCompletedLeg($now);

                if ($route->getStatus() === TradeRouteStatus::SINGLE_TRIP) {
                    $route->cancel();

                    return;
                }

                if ($route->hasReturn()) {
                    $loaded = $this->loadOnto(
                        $route->getTargetPlanet(),
                        $ship,
                        $route->getReturnResource(),
                        $route->getReturnQty(),
                    );
                    if ($loaded <= 0) {
                        // Wait — Return-Resource am Target nicht verfügbar.
                        return;
                    }
                }
                $this->moveFleet->__invoke($fleet->getId(), $route->getSourcePlanet()->getId());
                $route->setLeg(TradeRouteLeg::GOING_TO_SOURCE);

                return;

            case TradeRouteLeg::GOING_TO_SOURCE:
                if ($fleet->getStatus() === FleetStatus::DOCKED
                    && $ship->getPlanet()?->getId()->equals($route->getSourcePlanet()->getId()) === true
                ) {
                    if ($route->hasReturn()) {
                        $this->unloadInto(
                            $route->getSourcePlanet(),
                            $ship,
                            $route->getReturnResource(),
                        );
                    }
                    $route->recordCompletedLeg($now);
                    $route->setLeg(TradeRouteLeg::AT_SOURCE);
                }

                return;
        }
    }

    private function loadOnto(Planet $planet, Ship $ship, ResourceType $type, int $qty): int
    {
        $resource = $this->findResource($planet, $type);
        if ($resource === null) {
            return 0;
        }
        $available = $resource->getAmount();
        if ($available <= 0) {
            return 0;
        }
        $loadable = min($qty, $available, $ship->maxAddableResource($type, $qty));
        if ($loadable <= 0) {
            return 0;
        }
        $resource->setAmount($available - $loadable);
        $ship->loadResourceCargo($type, $loadable);

        return $loadable;
    }

    private function unloadInto(Planet $planet, Ship $ship, ResourceType $type): int
    {
        $loaded = $ship->getCargo()->getResource($type);
        if ($loaded <= 0) {
            return 0;
        }
        $room = $planet->maxAddableQuantity($type, $loaded);
        if ($room <= 0) {
            return 0;
        }
        $unloadable = min($loaded, $room);
        $resource = $planet->ensureResource($type);
        $resource->setAmount($resource->getAmount() + $unloadable);
        $ship->unloadResourceCargo($type, $unloadable);

        return $unloadable;
    }

    private function findResource(Planet $planet, ResourceType $type): ?Resource
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource;
            }
        }

        return null;
    }
}
