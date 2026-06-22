<?php

declare(strict_types=1);

namespace App\Player\ValueObject;

/**
 * T-122: Player-Background (40k-Imperial-Flavored). Permanent gewählt bei
 * Onboarding (T-046 noch nicht da) bzw. via `SetPlayerBackgroundCommand` aus
 * dem Demo-CLI. Foundation-only — die ±5%/-2% Multiplier-Effekte sind in
 * T-122b ausgelagert (warten auf Hooks in Mining/Reputation/Research/Probes/Trade).
 *
 * Unterschied zu T-098 Specialist-Tracks: Background ist Flavor + Mini-Boni
 * (±5%), Track ist Mechanik mit Branch-Lock (±30%). Beide kombinierbar.
 */
enum PlayerBackground: string
{
    /** Imperialer Adel — +5% Reputation-Speed, -2% Mining-Output */
    case IMPERIAL_NOBILITY = 'imperial_nobility';

    /** Aufsteiger (Common-Born) — +5% Mining-Output, -2% Reputation-Speed */
    case COMMON_BORN = 'common_born';

    /** Tech-Adept (Mechanicum) — +5% RP-Output, -2% Pop-Wachstum */
    case TECH_ADEPT = 'tech_adept';

    /** Veteran-Pilot — +5% Schiff-Speed/Combat-Crit, -2% Pop-Wachstum */
    case VETERAN_PILOT = 'veteran_pilot';

    /** Wanderer (Frontier-Born) — +5% Sonden-Range/Discovery-Speed, -2% Trade-Income */
    case FRONTIER_BORN = 'frontier_born';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::IMPERIAL_NOBILITY => 'Imperialer Adel',
            self::COMMON_BORN => 'Aufsteiger',
            self::TECH_ADEPT => 'Tech-Adept',
            self::VETERAN_PILOT => 'Veteran-Pilot',
            self::FRONTIER_BORN => 'Wanderer (Frontier)',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::IMPERIAL_NOBILITY => '+5% Reputation-Speed, -2% Mining-Output',
            self::COMMON_BORN => '+5% Mining-Output, -2% Reputation-Speed',
            self::TECH_ADEPT => '+5% Forschungs-Output, -2% Pop-Wachstum',
            self::VETERAN_PILOT => '+5% Schiff-Speed/Combat-Crit, -2% Pop-Wachstum',
            self::FRONTIER_BORN => '+5% Sonden-Range/Discovery-Speed, -2% Trade-Income',
        };
    }
}
