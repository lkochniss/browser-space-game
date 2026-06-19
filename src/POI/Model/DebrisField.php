<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceCategory;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * T-021 Trümmerfeld POI-Subtype.
 *
 * Hält Trümmer als Map<DEBRIS_*-ResourceType-value, count> in `debris_contents`
 * JSON-Spalte. Spawn-Quellen:
 *  - ShipSupplyProcessor.killShip(): kleines Mini-DebrisField bei Schiff-Tod
 *  - WorldFixture / Demo-Galaxy: deterministischer Spawn für Tests/Demo
 *  - T-103 Battle-Resolution (Folge): aus Verlusten
 *
 * Bergungsschiffe (T-016) extrahieren Trümmer in Cargo. RecyclingProcessor
 * konsumiert Trümmer auf Planet und konvertiert sie via Wahrscheinlichkeits-
 * Tabelle in zufällige FINITE/REFINED-Resources.
 */
#[ORM\Entity]
class DebrisField extends Poi implements SalvageableField
{
    /** @var array<string, int> Map<DEBRIS_*-ResourceType-value, count> */
    #[ORM\Column(name: 'debris_contents', type: 'json', nullable: true)]
    private array $contents = [];

    /**
     * @param array<string, int> $contents Map<DEBRIS_*-ResourceType-value, count>; nur DEBRIS-Resources erlaubt.
     */
    public function __construct(
        PoiId $id,
        SolarSystem $solarSystem,
        ?string $name = null,
        array $contents = [],
    ) {
        parent::__construct($id, $solarSystem, $name);
        foreach ($contents as $key => $amount) {
            $this->setAmount(ResourceType::from($key), $amount);
        }
    }

    public function getAmount(ResourceType $type): int
    {
        return $this->contents[$type->value] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function setAmount(ResourceType $type, int $amount): void
    {
        if ($type->getCategory() !== ResourceCategory::DEBRIS) {
            throw new InvalidArgumentException(sprintf(
                'DebrisField only accepts DEBRIS-category resources, got %s',
                $type->value,
            ));
        }
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf(
                'Debris amount must be >= 0, got %d for %s',
                $amount,
                $type->value,
            ));
        }
        if ($amount === 0) {
            unset($this->contents[$type->value]);

            return;
        }
        $this->contents[$type->value] = $amount;
    }

    public function extract(ResourceType $type, int $amount): int
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Extract amount must be > 0, got %d',
                $amount,
            ));
        }
        $available = $this->getAmount($type);
        $taken = min($amount, $available);
        $this->setAmount($type, $available - $taken);

        return $taken;
    }

    public function getTotalAmount(): int
    {
        return array_sum($this->contents);
    }

    public function isEmpty(): bool
    {
        return $this->getTotalAmount() === 0;
    }
}
