<?php

declare(strict_types=1);

namespace App\Trade\Repository;

use App\Player\Model\Player;
use App\Ship\Model\Ship;
use App\Trade\Model\TradeRoute;
use App\Trade\ValueObject\TradeRouteStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TradeRoute>
 */
class TradeRouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeRoute::class);
    }

    /** @return list<TradeRoute> */
    public function findActive(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.status IN (:running)')
            ->setParameter('running', [TradeRouteStatus::ACTIVE->value, TradeRouteStatus::SINGLE_TRIP->value])
            ->getQuery()
            ->getResult();
    }

    /** @return list<TradeRoute> */
    public function findByPlayer(Player $player): array
    {
        return $this->findBy(['owner' => $player]);
    }

    public function findByShip(Ship $ship): ?TradeRoute
    {
        return $this->createQueryBuilder('r')
            ->where('r.boundShip = :s')
            ->andWhere('r.status != :cancelled')
            ->setParameter('s', $ship)
            ->setParameter('cancelled', TradeRouteStatus::CANCELLED->value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
