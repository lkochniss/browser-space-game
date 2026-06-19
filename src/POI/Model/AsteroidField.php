<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * T-020 Asteroidenfeld POI-Subtype.
 *
 * Hält endliche Erz-Vorkommen als Map<ResourceType-value, int> in `asteroid_contents`-
 * JSON-Spalte. Bergungsschiffe (T-016) extrahieren Resources, transportieren zu
 * Planet/Station. Bei `getTotalAmount() == 0` ist das Feld erschöpft → POI wird
 * via T-016 Cleanup oder eigenem Tick-Processor entfernt.
 *
 * Foundation: nur FINITE-ResourceTypes (Erze). Erzeugnisse-/Tier-3-Varianten als
 * Folge-Erweiterung möglich.
 */
#[ORM\Entity]
class AsteroidField extends Poi
{
    /** @var array<string, int> Map<ResourceType-value, amount> */
    #[ORM\Column(name: 'asteroid_contents', type: 'json', nullable: true)]
    private array $contents = [];

    /**
     * @param array<string, int> $contents Map<ResourceType-value, amount>; nur FINITE-Resources erlaubt.
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
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf(
                'Asteroid amount must be >= 0, got %d for %s',
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

    /**
     * Reduziert das Vorkommen um $amount, klampt bei 0. Liefert die tatsächlich
     * extrahierte Menge zurück (kann kleiner sein als $amount, wenn weniger im
     * Feld ist).
     */
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
