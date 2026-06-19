<?php

declare(strict_types=1);

namespace App\Faction\Service;

use App\Faction\Exception\HostileFactionRepLockedException;
use App\Faction\Model\Faction;
use App\Faction\Model\PlayerFactionReputation;
use App\Faction\Repository\PlayerFactionReputationRepository;
use App\Faction\ValueObject\ReputationTier;
use App\Player\Model\Player;
use Doctrine\ORM\EntityManagerInterface;

readonly class ReputationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerFactionReputationRepository $repRepo,
    ) {
    }

    /**
     * Lazy-Lookup: gibt `defaultReputation` der Faction zurück, wenn keine Row existiert.
     */
    public function getReputation(Player $player, Faction $faction): int
    {
        $row = $this->repRepo->findByPlayerAndFaction($player, $faction);

        return $row?->getValue() ?? $faction->getDefaultReputation();
    }

    public function getTier(Player $player, Faction $faction): ReputationTier
    {
        return ReputationTier::forValue($this->getReputation($player, $faction));
    }

    /**
     * Mutation: legt Row lazy an (mit defaultReputation als Basis), addiert delta, clamped auf [-100, 100].
     *
     * @throws HostileFactionRepLockedException wenn Faction `isAlwaysHostile`
     */
    public function changeReputation(Player $player, Faction $faction, int $delta): int
    {
        if ($faction->isAlwaysHostile()) {
            throw new HostileFactionRepLockedException($faction);
        }

        $row = $this->repRepo->findByPlayerAndFaction($player, $faction);

        if ($row === null) {
            $row = new PlayerFactionReputation($player, $faction, $faction->getDefaultReputation());
            $this->em->persist($row);
        }

        $newValue = $this->clamp($row->getValue() + $delta);
        $row->setValue($newValue);

        return $newValue;
    }

    private function clamp(int $value): int
    {
        return max(PlayerFactionReputation::MIN_VALUE, min(PlayerFactionReputation::MAX_VALUE, $value));
    }
}
