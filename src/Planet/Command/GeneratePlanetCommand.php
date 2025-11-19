<?php

namespace App\Planet\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\Model\Planet;

/**
 * @implements CommandInterface<Planet>
 */
class GeneratePlanetCommand implements CommandInterface
{
    public function __construct()
    {
    }
}
