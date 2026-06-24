<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\ShipBlueprintNotFoundException;
use App\Ship\ValueObject\ShipBlueprint;
use App\Ship\ValueObject\ShipClass;

/**
 * T-102 Blueprint-Registry mit fest hinterlegten Stats für die 5 Combat-
 * Klassen × 3 Mk-Tiers (15 Werte).
 *
 * Skalierung: Mk II = Mk I × 1.5 Stats × 3× Cost. Mk III = Mk II × 1.5 × 3.
 * Cost-Mapping nutzt die T-067 Tier-2 Refined-Resources (Steel/Chip/Composite/
 * Hull-Plate) statt Raw-Erze — Combat-Schiffe sind High-End.
 */
class ShipBlueprintRegistry
{
    /** @var array<string,ShipBlueprint> Keyed by ShipClass.value */
    private array $blueprints;

    public function __construct()
    {
        $this->blueprints = [];

        $this->registerFamily(
            family: ShipClass::FRIGATE_MK1,
            mk1Hp: 1000,
            mk1Damage: 200,
            mk1Shield: 300,
            mk1Pop: 30,
            mk1DurationSec: 6 * 3600,
            mk1Cost: [
                ResourceType::STEEL->value => 500,
                ResourceType::IRON_BAR->value => 200,
            ],
        );

        $this->registerFamily(
            family: ShipClass::DESTROYER_MK1,
            mk1Hp: 2500,
            mk1Damage: 400,
            mk1Shield: 800,
            mk1Pop: 60,
            mk1DurationSec: 12 * 3600,
            mk1Cost: [
                ResourceType::STEEL->value => 1500,
                ResourceType::IRON_BAR->value => 500,
                ResourceType::CHIP->value => 50,
            ],
        );

        $this->registerFamily(
            family: ShipClass::CRUISER_MK1,
            mk1Hp: 5000,
            mk1Damage: 800,
            mk1Shield: 1500,
            mk1Pop: 120,
            mk1DurationSec: 36 * 3600,
            mk1Cost: [
                ResourceType::STEEL->value => 4000,
                ResourceType::IRON_BAR->value => 1500,
                ResourceType::CHIP->value => 200,
                ResourceType::COMPOSITE->value => 50,
            ],
        );

        $this->registerFamily(
            family: ShipClass::BATTLESHIP_MK1,
            mk1Hp: 12000,
            mk1Damage: 1500,
            mk1Shield: 3000,
            mk1Pop: 250,
            mk1DurationSec: 72 * 3600,
            mk1Cost: [
                ResourceType::STEEL->value => 10000,
                ResourceType::IRON_BAR->value => 3000,
                ResourceType::CHIP->value => 500,
                ResourceType::COMPOSITE->value => 200,
                ResourceType::HULL_PLATE->value => 50,
            ],
        );

        $this->registerFamily(
            family: ShipClass::CARRIER_MK1,
            mk1Hp: 8000,
            mk1Damage: 1800,
            mk1Shield: 1800,
            mk1Pop: 180,
            mk1DurationSec: 60 * 3600,
            mk1Cost: [
                ResourceType::STEEL->value => 7000,
                ResourceType::IRON_BAR->value => 2500,
                ResourceType::CHIP->value => 400,
                ResourceType::COMPOSITE->value => 150,
                ResourceType::HULL_PLATE->value => 30,
            ],
        );
    }

    public function get(ShipClass $class): ShipBlueprint
    {
        if (!isset($this->blueprints[$class->value])) {
            throw new ShipBlueprintNotFoundException($class);
        }

        return $this->blueprints[$class->value];
    }

    /**
     * @return list<ShipBlueprint>
     */
    public function all(): array
    {
        return array_values($this->blueprints);
    }

    /**
     * Registriert Mk I + leitet Mk II/III via Q1-Skalierungsformel ab.
     *
     * @param array<string,int> $mk1Cost
     */
    private function registerFamily(
        ShipClass $family,
        int $mk1Hp,
        int $mk1Damage,
        int $mk1Shield,
        int $mk1Pop,
        int $mk1DurationSec,
        array $mk1Cost,
    ): void {
        // Mk I direkt aus den übergebenen Stats.
        $this->register(new ShipBlueprint(
            class: $family,
            hp: $mk1Hp,
            damage: $mk1Damage,
            shieldCapacity: $mk1Shield,
            populationCost: $mk1Pop,
            buildDurationSeconds: $mk1DurationSec,
            buildCost: $mk1Cost,
            escapePodChance: $family->getEscapePodSurvivalChance(),
        ));

        // Mk II + Mk III via Skalierung.
        $statsFactor = 1.0;
        $costFactor = 1;
        $base = $family->value; // z.B. "frigate_mk1"
        $familyKey = $family->getFamily();
        for ($tier = 2; $tier <= 3; $tier++) {
            $statsFactor *= 1.5;
            $costFactor *= 3;
            $class = ShipClass::from(sprintf('%s_mk%d', $familyKey, $tier));
            $scaledCost = [];
            foreach ($mk1Cost as $resourceValue => $amount) {
                $scaledCost[$resourceValue] = (int) ceil($amount * $costFactor);
            }
            $this->register(new ShipBlueprint(
                class: $class,
                hp: (int) ceil($mk1Hp * $statsFactor),
                damage: (int) ceil($mk1Damage * $statsFactor),
                shieldCapacity: (int) ceil($mk1Shield * $statsFactor),
                populationCost: (int) ceil($mk1Pop * $statsFactor),
                buildDurationSeconds: (int) ceil($mk1DurationSec * $costFactor),
                buildCost: $scaledCost,
                escapePodChance: $class->getEscapePodSurvivalChance(),
            ));
        }
    }

    private function register(ShipBlueprint $bp): void
    {
        $this->blueprints[$bp->class->value] = $bp;
    }
}
