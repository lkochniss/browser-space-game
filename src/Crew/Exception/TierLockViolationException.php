<?php

declare(strict_types=1);

namespace App\Crew\Exception;

use App\Crew\ValueObject\CaptainSkillTree;

final class TierLockViolationException extends \DomainException
{
    public function __construct(CaptainSkillTree $tree, int $currentTier)
    {
        parent::__construct(sprintf(
            'Tree "%s" ist bereits auf Max-Tier %d — kein weiterer Punkt allokierbar.',
            $tree->value,
            $currentTier,
        ));
    }
}
