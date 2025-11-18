<?php

namespace App\Common\Interface;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
