<?php

declare(strict_types=1);

namespace App\Probe\Command;

use App\Common\Interface\CommandInterface;
use App\Planet\ValueObject\PlanetId;
use App\Probe\Model\Probe;
use App\Probe\ValueObject\ProbeType;

/**
 * @implements CommandInterface<Probe>
 */
class BuildProbeCommand implements CommandInterface
{
    public function __construct(
        public PlanetId $planetId,
        public ProbeType $type,
    ) {
    }
}
