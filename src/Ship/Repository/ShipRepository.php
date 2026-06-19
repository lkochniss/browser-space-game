<?php

declare(strict_types=1);

namespace App\Ship\Repository;

use App\Planet\Model\Planet;
use App\Ship\Model\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ship>
 */
class ShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    /**
     * @return list<Ship>
     */
    public function findByPlanet(Planet $planet): array
    {
        return $this->findBy(['planet' => $planet]);
    }
}
