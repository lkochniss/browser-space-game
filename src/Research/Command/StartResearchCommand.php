<?php

declare(strict_types=1);

namespace App\Research\Command;

use App\Common\Interface\CommandInterface;
use App\Player\ValueObject\PlayerId;

readonly class StartResearchCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public string $nodeSlug,
    ) {
    }
}
