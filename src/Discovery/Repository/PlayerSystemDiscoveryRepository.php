<?php

declare(strict_types=1);

namespace App\Discovery\Repository;

use App\Discovery\Model\PlayerSystemDiscovery;
use App\Player\Model\Player;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerSystemDiscovery>
 */
class PlayerSystemDiscoveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerSystemDiscovery::class);
    }

    /**
     * @return list<PlayerSystemDiscovery>
     */
    public function findByPlayer(Player $player): array
    {
        /** @var list<PlayerSystemDiscovery> */
        return $this->createQueryBuilder('d')
            ->andWhere('d.player = :p')
            ->setParameter('p', $player)
            ->getQuery()
            ->getResult();
    }

    public function isDiscovered(Player $player, SolarSystem $system): bool
    {
        return $this->createQueryBuilder('d')
            ->select('1')
            ->andWhere('d.player = :p')
            ->andWhere('d.solarSystem = :s')
            ->setParameter('p', $player)
            ->setParameter('s', $system)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
}
