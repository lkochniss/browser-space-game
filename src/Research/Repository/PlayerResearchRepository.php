<?php

declare(strict_types=1);

namespace App\Research\Repository;

use App\Player\Model\Player;
use App\Research\Model\PlayerResearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerResearch>
 */
class PlayerResearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerResearch::class);
    }

    public function findOneByPlayerAndSlug(Player $player, string $nodeSlug): ?PlayerResearch
    {
        return $this->findOneBy(['player' => $player, 'nodeSlug' => $nodeSlug]);
    }

    /**
     * @return list<PlayerResearch>
     */
    public function findByPlayer(Player $player): array
    {
        /** @var list<PlayerResearch> */
        return $this->findBy(['player' => $player]);
    }
}
