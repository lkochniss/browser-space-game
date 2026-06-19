<?php

declare(strict_types=1);

namespace App\Faction\Repository;

use App\Faction\Model\Faction;
use App\Faction\Model\PlayerFactionReputation;
use App\Player\Model\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerFactionReputation>
 */
class PlayerFactionReputationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerFactionReputation::class);
    }

    public function findByPlayerAndFaction(Player $player, Faction $faction): ?PlayerFactionReputation
    {
        return $this->findOneBy([
            'player' => $player,
            'faction' => $faction,
        ]);
    }
}
