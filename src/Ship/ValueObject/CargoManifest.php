<?php

declare(strict_types=1);

namespace App\Ship\ValueObject;

use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * T-015 Cargo-Manifest pro Schiff. Embedded VO.
 *
 * - `resources`: Map<ResourceType-value, int> (json column)
 * - `popCount`: Pop-Slot, semantisch separat von Resources (Doc: Pop ist lebendig).
 *
 * Capacity-Check (sumResources + popCount <= cargoCapacity) wird vom Ship gemacht,
 * nicht hier — VO ist dumm + immutable-style.
 */
#[ORM\Embeddable]
class CargoManifest
{
    public function __construct(
        /** @var array<string, int> */
        #[ORM\Column(type: 'json')]
        private array $resources = [],

        #[ORM\Column(name: 'pop_count', type: 'integer')]
        private int $popCount = 0,
    ) {
    }

    public static function empty(): self
    {
        return new self([], 0);
    }

    public function getResource(ResourceType $type): int
    {
        return $this->resources[$type->value] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    public function getPopCount(): int
    {
        return $this->popCount;
    }

    public function getTotalUnits(): int
    {
        return array_sum($this->resources) + $this->popCount;
    }

    public function isEmpty(): bool
    {
        return $this->getTotalUnits() === 0;
    }

    public function loadResource(ResourceType $type, int $amount): void
    {
        $this->requirePositive($amount, 'loadResource');
        $current = $this->resources[$type->value] ?? 0;
        $this->resources[$type->value] = $current + $amount;
    }

    public function unloadResource(ResourceType $type, int $amount): void
    {
        $this->requirePositive($amount, 'unloadResource');
        $current = $this->resources[$type->value] ?? 0;
        if ($current < $amount) {
            throw new InvalidArgumentException(sprintf(
                'Cannot unload %d %s: only %d in cargo',
                $amount,
                $type->value,
                $current,
            ));
        }
        $this->resources[$type->value] = $current - $amount;
        if ($this->resources[$type->value] === 0) {
            unset($this->resources[$type->value]);
        }
    }

    public function loadPop(int $amount): void
    {
        $this->requirePositive($amount, 'loadPop');
        $this->popCount += $amount;
    }

    public function unloadPop(int $amount): void
    {
        $this->requirePositive($amount, 'unloadPop');
        if ($amount > $this->popCount) {
            throw new InvalidArgumentException(sprintf(
                'Cannot unload %d pop: only %d in cargo',
                $amount,
                $this->popCount,
            ));
        }
        $this->popCount -= $amount;
    }

    private function requirePositive(int $amount, string $op): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException(sprintf('%s amount must be > 0, got %d', $op, $amount));
        }
    }
}
