<?php

declare(strict_types=1);

namespace App\Planet\Model;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Embeddable]
class Population
{
    public function __construct(
        #[ORM\Column(name: 'total', type: 'integer')]
        private int $total,

        #[ORM\Column(name: 'assigned', type: 'integer')]
        private int $assigned,

        #[ORM\Column(name: 'cap', type: 'integer')]
        private int $cap,
    ) {
        $this->validateInvariants();
    }

    public static function empty(int $cap = 100): self
    {
        return new self(total: 0, assigned: 0, cap: $cap);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getAssigned(): int
    {
        return $this->assigned;
    }

    public function getCap(): int
    {
        return $this->cap;
    }

    public function getFree(): int
    {
        return $this->total - $this->assigned;
    }

    public function assign(int $amount): void
    {
        $this->requireNonNegative($amount, 'assign');
        if ($amount > $this->getFree()) {
            throw new InvalidArgumentException(
                sprintf('Cannot assign %d: only %d free', $amount, $this->getFree())
            );
        }
        $this->assigned += $amount;
    }

    public function release(int $amount): void
    {
        $this->requireNonNegative($amount, 'release');
        if ($amount > $this->assigned) {
            throw new InvalidArgumentException(
                sprintf('Cannot release %d: only %d assigned', $amount, $this->assigned)
            );
        }
        $this->assigned -= $amount;
    }

    public function grow(int $amount): void
    {
        $this->requireNonNegative($amount, 'grow');
        $this->total = min($this->total + $amount, $this->cap);
    }

    /**
     * Kills population. Order: free first, then assigned (assigned units mean buildings/ships
     * lose their workers). Caller is responsible for resolving downstream effects.
     */
    public function kill(int $amount): void
    {
        $this->requireNonNegative($amount, 'kill');

        $killFromFree = min($amount, $this->getFree());
        $this->total -= $killFromFree;
        $remaining = $amount - $killFromFree;

        if ($remaining > 0) {
            $killFromAssigned = min($remaining, $this->assigned);
            $this->assigned -= $killFromAssigned;
            $this->total -= $killFromAssigned;
        }
    }

    public function setCap(int $cap): void
    {
        $this->requireNonNegative($cap, 'setCap');
        $this->cap = $cap;

        if ($this->total > $this->cap) {
            $this->total = $this->cap;
        }
        if ($this->assigned > $this->total) {
            $this->assigned = $this->total;
        }
    }

    private function validateInvariants(): void
    {
        if ($this->total < 0 || $this->assigned < 0 || $this->cap < 0) {
            throw new InvalidArgumentException('total/assigned/cap must be >= 0');
        }
        if ($this->assigned > $this->total) {
            throw new InvalidArgumentException(
                sprintf('assigned (%d) cannot exceed total (%d)', $this->assigned, $this->total)
            );
        }
        if ($this->total > $this->cap) {
            throw new InvalidArgumentException(
                sprintf('total (%d) cannot exceed cap (%d)', $this->total, $this->cap)
            );
        }
    }

    private function requireNonNegative(int $amount, string $op): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf('%s amount must be >= 0, got %d', $op, $amount));
        }
    }
}
