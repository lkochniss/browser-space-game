<?php

declare(strict_types=1);

namespace App\Research\Repository;

use App\Player\Model\Player;
use App\Research\Model\ActiveResearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActiveResearch>
 */
class ActiveResearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveResearch::class);
    }

    public function findActiveForPlayer(Player $player): ?ActiveResearch
    {
        return $this->findOneBy(['player' => $player]);
    }
}
