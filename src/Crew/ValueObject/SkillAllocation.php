<?php

declare(strict_types=1);

namespace App\Crew\ValueObject;

/**
 * T-104b Captain-Skill-Allocation. Map<TreeName.value, int>. Werte sind die
 * sequentiell allokierten Tiers pro Tree (0..MAX_TIER).
 *
 * Persistiert auf `Crew.skill_allocation` als JSON-Map. Sum aller Werte
 * ≤ Captain.level (1 Skill-Punkt pro Level).
 */
final readonly class SkillAllocation
{
    /** @var array<string,int> */
    public array $tiers;

    /**
     * @param array<string,int> $tiers
     */
    public function __construct(array $tiers = [])
    {
        $normalized = [];
        foreach (CaptainSkillTree::cases() as $tree) {
            $value = $tiers[$tree->value] ?? 0;
            $normalized[$tree->value] = max(0, min(CaptainSkillTree::MAX_TIER, (int) $value));
        }
        $this->tiers = $normalized;
    }

    public static function empty(): self
    {
        return new self();
    }

    public function getTier(CaptainSkillTree $tree): int
    {
        return $this->tiers[$tree->value] ?? 0;
    }

    public function totalPoints(): int
    {
        return array_sum($this->tiers);
    }

    /**
     * Liefert eine neue Allocation mit +1 Punkt im Tree (oder unverändert
     * wenn Max-Tier erreicht).
     */
    public function withIncrement(CaptainSkillTree $tree): self
    {
        $copy = $this->tiers;
        $copy[$tree->value] = min(CaptainSkillTree::MAX_TIER, ($copy[$tree->value] ?? 0) + 1);

        return new self($copy);
    }

    /** @return array<string,int> */
    public function toArray(): array
    {
        return $this->tiers;
    }
}
