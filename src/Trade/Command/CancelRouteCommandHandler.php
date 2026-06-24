<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Trade\Exception\TradeRouteNotFoundException;
use App\Trade\Model\TradeRoute;
use App\Trade\Repository\TradeRouteRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-110 Cancel: markiert Route als CANCELLED + löst Ship aus der dedizierten
 * Fleet (löscht die Fleet wenn sie nur dieses eine Schiff hatte).
 */
class CancelRouteCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TradeRouteRepository $repo,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(CancelRouteCommand $command): TradeRoute
    {
        $route = $this->repo->find($command->routeId)
            ?? throw new TradeRouteNotFoundException($command->routeId);

        $route->cancel();

        $ship = $route->getBoundShip();
        $fleet = $ship->getFleet();
        if ($fleet !== null) {
            $fleet->detachShip($ship);
            // Foundation: solo-Fleet löschen wenn nach Detach leer.
            if ($fleet->getShips()->isEmpty()) {
                $this->em->remove($fleet);
            }
        }

        $this->em->flush();

        return $route;
    }
}
