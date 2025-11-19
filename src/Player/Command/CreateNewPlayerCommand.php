<?php

namespace App\Player\Command;

use App\Common\Interface\CommandInterface;
use App\Player\Model\Player;

/**
 * @implements CommandInterface<Player>
 */
class CreateNewPlayerCommand implements CommandInterface
{
    public function __construct(
    )
    {
    }
}
