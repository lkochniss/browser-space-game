<?php

namespace App\Common\Interface;

interface CommandBusInterface
{
    /**
     * @template T of mixed
     * @param CommandInterface<T> $command
     * @return T
     */
    public function dispatch(CommandInterface $command): mixed;
}
