<?php

declare(strict_types=1);

namespace App\Crew\Repository;

use App\Crew\Model\Crew;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Player\Model\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crew>
 */
class CrewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crew::class);
    }

    /** @return list<Crew> */
    public function findByPlayer(Player $player): array
    {
        return $this->findBy(['owner' => $player]);
    }

    /** @return list<Crew> */
    public function findByPlayerAndType(Player $player, CrewType $type): array
    {
        return $this->findBy(['owner' => $player, 'type' => $type]);
    }

    /** @return list<Crew> */
    public function findIdleByPlayer(Player $player): array
    {
        return $this->findBy(['owner' => $player, 'status' => CrewStatus::IDLE]);
    }

    /** @return list<Crew> */
    public function findInTraining(): array
    {
        return $this->findBy(['status' => CrewStatus::TRAINING]);
    }

    /**
     * T-104a Cap-Counter: zählt alle non-DEAD Crew eines Players (alle Types).
     */
    public function countAliveByPlayer(Player $player): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.owner = :p')
            ->andWhere('c.status != :dead')
            ->setParameter('p', $player)
            ->setParameter('dead', CrewStatus::DEAD->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Zählt alle Crew eines Types eines Players, ohne DEAD (für Training-Formel).
     */
    public function countAliveByPlayerAndType(Player $player, CrewType $type): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.owner = :p')
            ->andWhere('c.type = :t')
            ->andWhere('c.status != :dead')
            ->setParameter('p', $player)
            ->setParameter('t', $type->value)
            ->setParameter('dead', CrewStatus::DEAD->value)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
