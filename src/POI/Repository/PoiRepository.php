<?php

declare(strict_types=1);

namespace App\POI\Repository;

use App\POI\Model\Poi;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poi>
 */
class PoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poi::class);
    }

    /**
     * @return list<Poi>
     */
    public function findBySolarSystem(SolarSystem $system): array
    {
        return $this->findBy(['solarSystem' => $system]);
    }
}
