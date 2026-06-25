<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipType;
use LogicException;

/**
 * T-178: Cargo-Volume-Capacity (m³) pro Ship-Type / Ship-Class.
 *
 * Spezial-Schiffe (ShipType) haben konstanten m³-Wert. Combat-Schiffe
 * (ShipClass) haben Base × Mk-Multi (Mk I 1.0, Mk II 1.5, Mk III 2.25 —
 * analog T-102 Stats-Scaling).
 */
class ShipCargoVolumeConfig
{
    /** @var array<string, int> ShipType.value → cargoVolume (m³) */
    private const SHIP_TYPE_VOLUME = [
        ShipType::GENERIC->value => 50,
        ShipType::COLONY_SHIP->value => 300,
        ShipType::TRANSPORT_SMALL->value => 100,
        ShipType::TRANSPORT_MEDIUM->value => 500,
        ShipType::TRANSPORT_LARGE->value => 2000,
        ShipType::SALVAGE->value => 500,
    ];

    /** @var array<string, int> ShipClass-family → Base-Volume (Mk I) */
    private const COMBAT_FAMILY_BASE_VOLUME = [
        'frigate' => 50,
        'destroyer' => 80,
        'cruiser' => 120,
        'battleship' => 200,
        'carrier' => 150,
    ];

    /** Mk-Tier Multiplier — analog T-102 Stats-Scaling. */
    private const MK_MULTIPLIER = [
        1 => 1.0,
        2 => 1.5,
        3 => 2.25,
    ];

    /**
     * Hauptlookup: liefert Cargo-Volume für Spezial-Schiffe (per ShipType)
     * ODER Combat-Schiffe (per ShipClass mit Mk-Multi).
     */
    public function getCargoVolume(ShipType $type, ?ShipClass $shipClass = null): int
    {
        if ($shipClass !== null) {
            return $this->getCombatVolume($shipClass);
        }

        if (!isset(self::SHIP_TYPE_VOLUME[$type->value])) {
            throw new LogicException(sprintf('No cargo volume configured for ShipType "%s"', $type->value));
        }

        return self::SHIP_TYPE_VOLUME[$type->value];
    }

    private function getCombatVolume(ShipClass $class): int
    {
        $family = $class->getFamily();
        if (!isset(self::COMBAT_FAMILY_BASE_VOLUME[$family])) {
            throw new LogicException(sprintf('No base cargo volume for ship-class family "%s"', $family));
        }
        $base = self::COMBAT_FAMILY_BASE_VOLUME[$family];
        $multi = self::MK_MULTIPLIER[$class->getTier()] ?? 1.0;

        return (int) round($base * $multi);
    }
}
