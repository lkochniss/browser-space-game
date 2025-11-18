<?php

namespace App\Tick\Interface;
use App\Planet\Model\Planet;

interface TickProcessorInterface
{
    public function process(Planet $planet): void;
}
