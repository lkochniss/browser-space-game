<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;

/**
 * T-021: Marker-Interface für POIs, die per Bergungsschiff (T-016) abgetragen
 * werden können. AsteroidField + DebrisField implementieren das.
 *
 * SalvageProcessor + StartSalvageCommandService prüfen `instanceof SalvageableField`
 * statt nur AsteroidField.
 */
interface SalvageableField
{
    public function getSolarSystem(): SolarSystem;

    public function getAmount(ResourceType $type): int;

    /**
     * @return array<string, int>
     */
    public function getContents(): array;

    public function setAmount(ResourceType $type, int $amount): void;

    public function extract(ResourceType $type, int $amount): int;

    public function getTotalAmount(): int;

    public function isEmpty(): bool;
}
