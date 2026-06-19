<?php

declare(strict_types=1);

namespace App\Probe\Repository;

use App\Planet\Model\Planet;
use App\Probe\Model\Probe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Probe>
 */
class ProbeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Probe::class);
    }

    /**
     * @return list<Probe>
     */
    public function findByPlanet(Planet $planet): array
    {
        return $this->findBy(['planet' => $planet]);
    }
}
