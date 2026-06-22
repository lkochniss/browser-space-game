<?php

declare(strict_types=1);

namespace App\Player\Command;

use App\Common\Interface\CommandInterface;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerBackground;
use App\Player\ValueObject\PlayerId;

/**
 * @implements CommandInterface<Player>
 */
readonly class SetPlayerBackgroundCommand implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerBackground $background,
    ) {
    }
}
