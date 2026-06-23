<?php

declare(strict_types=1);

namespace App\Resource\Service;

use App\Resource\Exception\UnknownResourceVolumeException;
use App\Resource\ValueObject\ResourceType;

/**
 * T-180: Foundation für Generic-Storage-System (T-177ff).
 *
 * Definiert wie viel **physikalisches Volumen (m³)** 1 Einheit jeder Resource
 * sowie 1 Pop belegt. Diese Werte werden vom Storage-System (Folge-Tickets)
 * konsumiert, um Storage-Cap planet-übergreifend in einer Einheit zu
 * berechnen statt pro-Resource separat.
 *
 * Reference-Einheit: **1 m³**.
 *
 * Balance-Vorschlag aus T-180 — Detail-Justierung in Folge-Tickets nach Playtest.
 */
class ResourceVolumeConfig
{
    /** T-172: Working-Pop braucht Lebensraum (Wohnen + Versorgung) = 10 m³. */
    public const POP_MULTIPLIER = 10.0;

    /**
     * @var array<string, float> Map<ResourceType.value, m³ per Unit>
     */
    private const MULTIPLIERS = [
        // Renewables (Liquid/Gas)
        ResourceType::WATER->value => 1.0,        // Reference (1t ≈ 1m³)
        ResourceType::FOOD->value => 1.2,         // Verpackung, Kühlung
        ResourceType::OXYGEN->value => 0.3,       // Gas, komprimiert

        // Finite Erze
        ResourceType::IRON_ORE->value => 2.0,     // Sperrig (Brocken mit Luftlücken)
        ResourceType::COAL->value => 1.8,         // Leichter als Iron-Ore
        ResourceType::COPPER_ORE->value => 2.0,   // Wie Iron-Ore
        ResourceType::SILICON->value => 1.8,      // Leichter als Eisen
        ResourceType::ALUMINUM_ORE->value => 2.0, // Analog Iron/Copper
        ResourceType::TITANIUM_ORE->value => 2.0, // Analog Iron/Copper
        ResourceType::URANIUM_ORE->value => 2.5,  // Radioaktiv → Bleicontainer

        // T-067 Tier-2 FINITE Erze
        ResourceType::PLASTIC_RESIN->value => 1.5, // Halbflüssig, Verpackung
        ResourceType::TRITIUM_ORE->value => 2.0,   // Erz, sperrig

        // Refined (Tier-1)
        ResourceType::IRON_BAR->value => 1.5,     // Refined kompakter als Erz

        // T-067 Tier-2 REFINED (Bars + Compounds)
        ResourceType::ALUMINUM_BAR->value => 0.8,   // Leicht, refined kompakt
        ResourceType::COPPER_BAR->value => 1.4,    // Schwerer als Aluminum, kompakt
        ResourceType::TITANIUM_BAR->value => 1.0,  // Mittlere Dichte refined
        ResourceType::STEEL->value => 1.0,         // Industrieprodukt (Tier-2-Default)
        ResourceType::CHIP->value => 0.3,          // Klein, hochwertig pro Volumen
        ResourceType::COMPOSITE->value => 1.2,     // Sandwich-Material, leichter Bulk
        ResourceType::HULL_PLATE->value => 2.5,    // Großflächig, sperrig
        ResourceType::SHIELD_MODULE->value => 0.8, // Kompakte Einheit

        // Debris (T-021)
        ResourceType::DEBRIS_LOW->value => 1.0,
        ResourceType::DEBRIS_MEDIUM->value => 1.0,
        ResourceType::DEBRIS_HIGH->value => 1.0,
    ];

    public static function getMultiForResource(ResourceType $type): float
    {
        if (!isset(self::MULTIPLIERS[$type->value])) {
            throw new UnknownResourceVolumeException($type);
        }

        return self::MULTIPLIERS[$type->value];
    }

    public static function getPopMulti(): float
    {
        return self::POP_MULTIPLIER;
    }

    /**
     * Map<resourceVal, m³/Unit> — primär für Debug / Doc-Generation.
     *
     * @return array<string, float>
     */
    public static function all(): array
    {
        return self::MULTIPLIERS;
    }
}
