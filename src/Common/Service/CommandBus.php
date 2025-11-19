<?php

namespace App\Common\Service;

use App\Common\Interface\CommandBusInterface;
use App\Common\Interface\CommandInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

readonly class CommandBus implements CommandBusInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    public function dispatch(CommandInterface $command): mixed
    {
        $commandClass = get_class($command);
        $handlerClass = $commandClass . 'Handler';

        if (!$this->container->has($handlerClass)) {
            throw new RuntimeException("No handler found for $commandClass");
        }

        $handler = $this->container->get($handlerClass);

        return $handler($command);
    }
}
