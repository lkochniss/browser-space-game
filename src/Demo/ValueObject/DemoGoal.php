<?php

declare(strict_types=1);

namespace App\Demo\ValueObject;

readonly class DemoGoal
{
    public function __construct(
        public string $label,
        public bool $completed,
        public string $progressHint = '',
    ) {
    }
}
