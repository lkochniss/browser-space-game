<?php

declare(strict_types=1);

namespace App\Ship\Command;

use App\Common\Interface\CommandInterface;
use App\Ship\Model\Ship;
use App\Ship\ValueObject\ShipId;

/**
 * @implements CommandInterface<Ship>
 *
 * Lädt Resources + Pop vom Heimat-Planet ins Schiff (T-015).
 */
class LoadCargoCommand implements CommandInterface
{
    /**
     * @param array<string, int> $resources Map<ResourceType-value, amount>
     */
    public function __construct(
        public ShipId $shipId,
        public array $resources = [],
        public int $popCount = 0,
    ) {
    }
}
