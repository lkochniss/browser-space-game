<?php

declare(strict_types=1);

namespace App\Trade\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Trade\Exception\TradeRouteNotFoundException;
use App\Trade\Model\TradeRoute;
use App\Trade\Repository\TradeRouteRepository;
use Doctrine\ORM\EntityManagerInterface;

class ResumeRouteCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TradeRouteRepository $repo,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(ResumeRouteCommand $command): TradeRoute
    {
        $route = $this->repo->find($command->routeId)
            ?? throw new TradeRouteNotFoundException($command->routeId);
        $route->resume();
        $this->em->flush();

        return $route;
    }
}
