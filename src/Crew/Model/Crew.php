<?php

declare(strict_types=1);

namespace App\Crew\Model;

use App\Common\Doctrine\Type\CrewIdType;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Player\Model\Player;
use App\Ship\Model\Ship;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-104a Crew-Foundation: Captain als limited Resource für Combat-Schiffe.
 *
 * Lebens-Zyklus:
 *   TRAINING → IDLE → ASSIGNED ↔ IDLE → DEAD (bei Battle-Loss ohne Escape-Pod)
 *
 * Stats-Bonus auf Schiff: `+3%/Level` Damage/HP/Shield (T-104a Q2).
 * Level 1-10. XP-basiert (Combat-Auto) + Akademie-Boost (T-104a Q3).
 * Permadeath via Escape-Pod-Roll bei Schiff-Verlust (T-104a Q4 + T-102 Q3).
 */
#[ORM\Entity(repositoryClass: CrewRepository::class)]
#[ORM\Table(name: 'crew')]
class Crew
{
    public const MAX_LEVEL = 10;
    public const STATS_BONUS_PER_LEVEL = 0.03;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: CrewIdType::NAME)]
        private CrewId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
        private Player $owner,

        #[ORM\Column(name: 'type', type: 'string', length: 16, enumType: CrewType::class)]
        private CrewType $type,

        #[ORM\Column(name: 'status', type: 'string', length: 16, enumType: CrewStatus::class)]
        private CrewStatus $status,

        #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 1])]
        private int $level = 1,

        #[ORM\Column(name: 'xp', type: 'integer', options: ['default' => 0])]
        private int $xp = 0,

        #[ORM\ManyToOne(targetEntity: Ship::class)]
        #[ORM\JoinColumn(name: 'assigned_ship_id', referencedColumnName: 'id', nullable: true)]
        private ?Ship $assignedShip = null,

        #[ORM\Column(name: 'training_finished_at', type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $trainingFinishedAt = null,

        #[ORM\Column(name: 'last_boost_at', type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $lastBoostAt = null,

        /**
         * T-104b Captain-Skill-Tree-Allocation. JSON-Map<TreeName.value, int>.
         * Hydration über Doctrine `json`-Type; Domain-Logic wraps in
         * `SkillAllocation`-VO (s. getSkillAllocation).
         *
         * @var array<string,int>
         */
        #[ORM\Column(name: 'skill_allocation', type: 'json', options: ['default' => '{}'])]
        private array $skillAllocationRaw = [],
    ) {
    }

    public static function startTraining(
        Player $owner,
        CrewType $type,
        DateTimeImmutable $finishedAt,
    ): self {
        return new self(
            id: CrewId::generate(),
            owner: $owner,
            type: $type,
            status: CrewStatus::TRAINING,
            level: 1,
            xp: 0,
            assignedShip: null,
            trainingFinishedAt: $finishedAt,
        );
    }

    public function getId(): CrewId
    {
        return $this->id;
    }

    public function getOwner(): Player
    {
        return $this->owner;
    }

    public function getType(): CrewType
    {
        return $this->type;
    }

    public function getStatus(): CrewStatus
    {
        return $this->status;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function getAssignedShip(): ?Ship
    {
        return $this->assignedShip;
    }

    public function getTrainingFinishedAt(): ?DateTimeImmutable
    {
        return $this->trainingFinishedAt;
    }

    public function getLastBoostAt(): ?DateTimeImmutable
    {
        return $this->lastBoostAt;
    }

    /**
     * Training fertig → IDLE.
     */
    public function completeTraining(): void
    {
        $this->status = CrewStatus::IDLE;
        $this->trainingFinishedAt = null;
    }

    public function isTrainingDone(DateTimeImmutable $now): bool
    {
        return $this->status === CrewStatus::TRAINING
            && $this->trainingFinishedAt !== null
            && $this->trainingFinishedAt <= $now;
    }

    public function assignToShip(Ship $ship): void
    {
        $this->assignedShip = $ship;
        $this->status = CrewStatus::ASSIGNED;
    }

    public function unassign(): void
    {
        $this->assignedShip = null;
        $this->status = CrewStatus::IDLE;
    }

    public function markDead(): void
    {
        $this->status = CrewStatus::DEAD;
        $this->assignedShip = null;
    }

    /**
     * T-104a XP-System: addiert XP, auto-Level-Up bei Threshold.
     * Threshold-Table: kumulativ.
     */
    public function addXp(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        $this->xp += $amount;
        while ($this->level < self::MAX_LEVEL && $this->xp >= self::xpThresholdForLevel($this->level + 1)) {
            ++$this->level;
        }
    }

    public function recordBoost(DateTimeImmutable $now): void
    {
        $this->lastBoostAt = $now;
    }

    public function getSkillAllocation(): \App\Crew\ValueObject\SkillAllocation
    {
        return new \App\Crew\ValueObject\SkillAllocation($this->skillAllocationRaw);
    }

    /**
     * T-104b verfügbare Skill-Punkte: 1 pro Captain-Level minus bereits
     * allokierte. Eingabe für Allocate-Command.
     */
    public function availableSkillPoints(): int
    {
        return $this->level - $this->getSkillAllocation()->totalPoints();
    }

    /**
     * T-104b: allokiert 1 Punkt in den angegebenen Tree. Tier-Lock ist
     * implizit (sequentiell — Tier-N braucht (N-1) Vorgänger-Punkte).
     * Caller (Service) prüft `availableSkillPoints` + Max-Tier vorab.
     */
    public function applySkillAllocation(\App\Crew\ValueObject\SkillAllocation $allocation): void
    {
        $this->skillAllocationRaw = $allocation->toArray();
    }

    /**
     * T-104b Read-API für T-103b Battle-Tactic-Wiring. Foundation: keine
     * Battle-Resolver-Konsum — T-104b liefert nur die Multiplier-Lookups.
     */
    public function getSkillTier(\App\Crew\ValueObject\CaptainSkillTree $tree): int
    {
        return $this->getSkillAllocation()->getTier($tree);
    }

    /**
     * T-104b: Beam-Master ODER Missile-Specialist Damage-Multi gegeben
     * Tactic-Match-Flag. Caller (Battle-Resolver) decided welcher Tree für
     * die aktuelle Tactic gilt.
     */
    public function getDamageMultiplier(\App\Crew\ValueObject\CaptainSkillTree $tree): float
    {
        return $tree->getDamageMultiplierAtTier($this->getSkillTier($tree));
    }

    public function getShieldMultiplier(): float
    {
        $tree = \App\Crew\ValueObject\CaptainSkillTree::SHIELD_TACTICIAN;

        return $tree->getShieldMultiplierAtTier($this->getSkillTier($tree));
    }

    public function getFleetCommanderTier(): int
    {
        return $this->getSkillTier(\App\Crew\ValueObject\CaptainSkillTree::FLEET_COMMANDER);
    }

    /**
     * Cumulative XP-Threshold benötigt um Level zu erreichen.
     *   L2 = 100, L3 = 250, L4 = 500, L5 = 1000, ..., L10 = 5000.
     */
    public static function xpThresholdForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }
        return match (true) {
            $level === 2 => 100,
            $level === 3 => 250,
            $level === 4 => 500,
            $level === 5 => 1000,
            $level === 6 => 1750,
            $level === 7 => 2500,
            $level === 8 => 3250,
            $level === 9 => 4000,
            $level === 10 => 5000,
            default => PHP_INT_MAX,
        };
    }

    /**
     * Stats-Bonus-Multiplier (für Ship.effectiveDamage/HP/Shield wenn ASSIGNED).
     */
    public function getStatsMultiplier(): float
    {
        if ($this->status !== CrewStatus::ASSIGNED) {
            return 1.0;
        }
        return 1.0 + self::STATS_BONUS_PER_LEVEL * $this->level;
    }
}
