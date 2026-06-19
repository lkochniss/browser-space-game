<?php

declare(strict_types=1);

namespace App\SolarSystem\Repository;

use App\SolarSystem\Model\SolarSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SolarSystem>
 */
class SolarSystemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SolarSystem::class);
    }
}
