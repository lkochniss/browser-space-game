<?php

declare(strict_types=1);

namespace App\Tests\Resource\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class DebugResourceVolumeCommandTest extends KernelTestCase
{
    public function test_command_runs_and_lists_resources(): void
    {
        self::bootKernel();
        $app = new Application(self::$kernel);
        $tester = new CommandTester($app->find('app:debug:resource-volume'));

        $exitCode = $tester->execute([]);

        self::assertSame(0, $exitCode);
        $output = $tester->getDisplay();

        // Header
        self::assertStringContainsString('Resource-Volume-Multi', $output);
        self::assertStringContainsString('Pop-Multi', $output);
        // Mindestens 1 Resource in Tabelle
        self::assertStringContainsString('iron_ore', $output);
        self::assertStringContainsString('water', $output);
        // Beispiel-Sektion
        self::assertStringContainsString('Beispiel-Storage-Berechnungen', $output);
        // Profil-Sektion
        self::assertStringContainsString('Typisches Storage-Profil', $output);
    }
}
