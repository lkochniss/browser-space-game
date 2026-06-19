<?php

declare(strict_types=1);

namespace App\Faction\Repository;

use App\Faction\Model\Faction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faction>
 */
class FactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faction::class);
    }

    public function findBySlug(string $slug): ?Faction
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
