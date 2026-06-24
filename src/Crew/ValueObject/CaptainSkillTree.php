<?php

declare(strict_types=1);

namespace App\Crew\ValueObject;

/**
 * T-104b Captain-Skill-Trees. 4 Bäume × 5 Tiers — Captain allokiert frei
 * (1 Punkt pro Captain-Level, max 10) mit strikt sequenzieller Tier-Lock
 * (Tier-N braucht (N-1) Punkte in demselben Tree).
 */
enum CaptainSkillTree: string
{
    case BEAM_MASTER = 'beam_master';
    case MISSILE_SPECIALIST = 'missile_specialist';
    case SHIELD_TACTICIAN = 'shield_tactician';
    case FLEET_COMMANDER = 'fleet_commander';

    public const MAX_TIER = 5;

    /**
     * T-104b Damage-Multi pro Tier (Beam-Master + Missile-Specialist gleich).
     * Tactic-Context (Standoff/Flanking) wird im Battle-Resolver matched.
     */
    public function getDamageMultiplierAtTier(int $tier): float
    {
        if ($this !== self::BEAM_MASTER && $this !== self::MISSILE_SPECIALIST) {
            return 1.0;
        }

        return match (max(0, min(self::MAX_TIER, $tier))) {
            0 => 1.0,
            1 => 1.05,
            2 => 1.12,
            3 => 1.20,
            4 => 1.30,
            5 => 1.42,
        };
    }

    /**
     * T-104b Shield-Tactician Shield-HP-Multi (Front-Assault-Tactic).
     */
    public function getShieldMultiplierAtTier(int $tier): float
    {
        if ($this !== self::SHIELD_TACTICIAN) {
            return 1.0;
        }

        return match (max(0, min(self::MAX_TIER, $tier))) {
            0 => 1.0,
            1 => 1.10,
            2 => 1.25,
            3 => 1.45,
            4 => 1.70,
            5 => 2.00,
        };
    }

    /**
     * T-104b Fleet-Commander Tactic-Counter-Boost. +0.04 pro Tier additiv
     * auf Base 1.3 (T-103b Tactic-Multi). Tier 5 = +0.20 → Counter ×1.50.
     */
    public function getFleetCommanderBoost(int $tier): float
    {
        if ($this !== self::FLEET_COMMANDER) {
            return 0.0;
        }

        return 0.04 * max(0, min(self::MAX_TIER, $tier));
    }
}
