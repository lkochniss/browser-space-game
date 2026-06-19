<?php

declare(strict_types=1);

namespace App\Fleet\Repository;

use App\Fleet\Model\Fleet;
use App\Fleet\ValueObject\FleetStatus;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fleet>
 */
class FleetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    /**
     * Findet alle Fleets die bis $now angekommen sind (status=IN_TRANSIT, arrivedAt<=now).
     *
     * @return list<Fleet>
     */
    public function findArrivedFleets(DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :status')
            ->andWhere('f.arrivedAt <= :now')
            ->setParameter('status', FleetStatus::IN_TRANSIT->value)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
}
