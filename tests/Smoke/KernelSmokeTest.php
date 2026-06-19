<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Common\Interface\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class KernelSmokeTest extends KernelTestCase
{
    public function test_kernel_boots_and_container_is_available(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        self::assertNotNull($container);
    }

    public function test_command_bus_is_wired(): void
    {
        self::bootKernel();

        $bus = self::getContainer()->get(CommandBusInterface::class);
        self::assertInstanceOf(CommandBusInterface::class, $bus);
    }
}
