<?php

declare(strict_types=1);

namespace App\Battle\Service;

use App\Battle\Model\Battle;
use App\Battle\ValueObject\BattleStatus;
use App\Common\Interface\ClockInterface;
use App\Crew\Repository\CrewRepository;
use App\Fleet\Model\Fleet;
use App\Planet\Model\Planet;
use App\Ship\Model\Ship;
use App\Ship\Service\ShipBlueprintRegistry;
use App\Ship\ValueObject\ShipClass;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-103 Battle-Resolution-Engine (Foundation). Synchroner Round-by-Round
 * Resolver. Out-of-Scope: Tactic-RPS (T-103b), NPC-AI (T-103c), Replay-Log
 * (T-103d), Loot-Trigger (T-103e).
 *
 * Damage-Modell:
 *  - Pro Schiff: HP-Pool + Damage-Stat (T-102 Blueprint, mit Captain-Boost
 *    via Crew T-104a)
 *  - Round = Σ Side-Damage, gleichmäßig auf Gegner-Schiffe verteilt
 *  - Planet-Defense (T-068): Shield-HP absorbiert vor Schiff-Damage;
 *    Turret + AA-Damage zählt zu Defender-Side
 *  - Schiff stirbt bei HP <= 0 → `em->remove()`; Captain-Permadeath-Roll
 *
 * Round-Limit: 10. Draw wenn beide Seiten leben nach Limit.
 */
readonly class BattleResolver
{
    public const MAX_ROUNDS = 10;
    public const NON_COMBAT_FALLBACK_HP = 100;
    public const NON_COMBAT_FALLBACK_DAMAGE = 50;

    public function __construct(
        private EntityManagerInterface $em,
        private ShipBlueprintRegistry $blueprints,
        private CrewRepository $crewRepo,
        private ClockInterface $clock,
        private BattleRandomizer $randomizer = new BattleRandomizer(),
    ) {
    }

    public function resolve(Battle $battle): void
    {
        $attackerShips = $this->ships($battle->getAttackerFleet());
        $defenderShips = $this->ships($battle->getDefenderFleet());
        $defenderPlanet = $battle->getDefenderPlanet();

        $this->initBattleHp($attackerShips);
        $this->initBattleHp($defenderShips);

        // Defender-Shield-Pool (T-068) — Snapshot to mutate during Battle.
        $shieldHp = 0;
        if ($defenderPlanet !== null) {
            $shieldHp = $defenderPlanet->getDefenseStats($this->clock->now())->shieldHp;
        }

        for ($round = 0; $round < self::MAX_ROUNDS; $round++) {
            if ($this->allDead($attackerShips) || ($this->allDead($defenderShips) && $defenderPlanet === null)) {
                break;
            }

            $attackerDamage = $this->totalDamage($attackerShips);
            $defenderDamage = $this->totalDamage($defenderShips);
            if ($defenderPlanet !== null) {
                $stats = $defenderPlanet->getDefenseStats($this->clock->now());
                $defenderDamage += $stats->turretDamage + $stats->aaDamage;
            }

            // Damage gegen Defender-Side
            if ($attackerDamage > 0) {
                $effective = $attackerDamage;
                if ($shieldHp > 0) {
                    $absorbed = min($shieldHp, $effective);
                    $shieldHp -= $absorbed;
                    $effective -= $absorbed;
                }
                if ($effective > 0) {
                    $this->distributeDamage($defenderShips, $effective);
                }
            }

            // Damage gegen Attacker-Side (kein Shield für Attacker — Planet-Defense
            // ist asymmetrisch zugunsten Defender)
            if ($defenderDamage > 0) {
                $this->distributeDamage($attackerShips, $defenderDamage);
            }

            $this->killDead($attackerShips);
            $this->killDead($defenderShips);

            $battle->incrementRound();

            if ($this->allDead($attackerShips)) {
                $this->finishBattle($battle, BattleStatus::ENDED_DEFENDER_WIN);

                return;
            }
            if ($this->allDead($defenderShips) && $defenderPlanet === null) {
                $this->finishBattle($battle, BattleStatus::ENDED_ATTACKER_WIN);

                return;
            }
        }

        // Round-Limit erreicht → DRAW.
        $this->finishBattle($battle, BattleStatus::DRAW);
    }

    /**
     * @return list<Ship>
     */
    private function ships(?Fleet $fleet): array
    {
        if ($fleet === null) {
            return [];
        }

        return $fleet->getShips()->toArray();
    }

    /**
     * @param list<Ship> $ships
     */
    private function initBattleHp(array $ships): void
    {
        foreach ($ships as $ship) {
            if ($ship->getBattleCurrentHp() !== null) {
                continue;
            }
            $ship->setBattleCurrentHp($this->maxHp($ship));
        }
    }

    /**
     * @param list<Ship> $ships
     */
    private function totalDamage(array $ships): int
    {
        $sum = 0;
        foreach ($ships as $ship) {
            if (($ship->getBattleCurrentHp() ?? 0) <= 0) {
                continue;
            }
            $sum += $this->effectiveDamage($ship);
        }

        return $sum;
    }

    /**
     * @param list<Ship> $defenders
     */
    private function distributeDamage(array $defenders, int $totalDamage): void
    {
        $alive = array_values(array_filter(
            $defenders,
            fn (Ship $s) => ($s->getBattleCurrentHp() ?? 0) > 0,
        ));
        $n = count($alive);
        if ($n === 0) {
            return;
        }
        $perShip = intdiv($totalDamage, $n);
        if ($perShip <= 0) {
            return;
        }
        foreach ($alive as $ship) {
            $hp = $ship->getBattleCurrentHp() ?? 0;
            $ship->setBattleCurrentHp(max(0, $hp - $perShip));
        }
    }

    /**
     * @param list<Ship> $ships
     */
    private function killDead(array $ships): void
    {
        foreach ($ships as $ship) {
            if (($ship->getBattleCurrentHp() ?? 1) > 0) {
                continue;
            }
            $this->captainPermadeathRoll($ship);
            $this->em->remove($ship);
        }
    }

    /**
     * T-104a × T-102 Permadeath-Roll. Wenn Captain assigned: per Pod-Chance
     * survived (IDLE auf Heimat-Planet, assignedShip=null) oder DEAD.
     */
    private function captainPermadeathRoll(Ship $ship): void
    {
        $captain = $this->crewRepo->findByAssignedShip($ship);
        if ($captain === null) {
            return;
        }

        $podChance = $ship->getEscapePodSurvivalChance();
        $roll = $this->randomizer->roll();
        if ($roll < $podChance) {
            $captain->unassign();

            return;
        }
        $captain->markDead();
    }

    /**
     * @param list<Ship> $ships
     */
    private function allDead(array $ships): bool
    {
        foreach ($ships as $ship) {
            if (($ship->getBattleCurrentHp() ?? 1) > 0) {
                return false;
            }
        }

        return true;
    }

    private function maxHp(Ship $ship): int
    {
        $class = $ship->getShipClass();
        if ($class instanceof ShipClass) {
            return $this->blueprints->get($class)->hp;
        }

        return self::NON_COMBAT_FALLBACK_HP;
    }

    /**
     * T-103: Captain-Stats-Boost (T-104a `1 + 0.03 × level`) multiplikativ.
     */
    private function effectiveDamage(Ship $ship): int
    {
        $class = $ship->getShipClass();
        $base = $class instanceof ShipClass
            ? $this->blueprints->get($class)->damage
            : self::NON_COMBAT_FALLBACK_DAMAGE;
        $captain = $this->crewRepo->findByAssignedShip($ship);
        $multi = $captain?->getStatsMultiplier() ?? 1.0;

        return (int) floor($base * $multi);
    }

    private function finishBattle(Battle $battle, BattleStatus $status): void
    {
        $battle->endWith($status, $this->clock->now());
        $this->em->flush();
    }
}
