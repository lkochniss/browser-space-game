<?php

declare(strict_types=1);

namespace App\Resource\Command;

use App\Resource\Service\ResourceVolumeConfig;
use App\Resource\ValueObject\ResourceType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * T-181 Debug-Tooling für `ResourceVolumeConfig` (T-180 Foundation).
 *
 * Listet alle Volume-Multiplier in m³/Unit + Beispiel-Storage-Berechnungen.
 * Hilfreich für Balancing-Sessions (z.B. "wie viel m³ kostet ein typischer
 * Storage-Plan mit 1000 Pop + 5000 Iron + 5000 Water?").
 *
 * Aufruf: `bin/console app:debug:resource-volume`
 */
#[AsCommand(
    name: 'app:debug:resource-volume',
    description: 'T-181: Listet Resource-Volume-Multi (m³/Unit) + Beispiel-Berechnungen',
)]
class DebugResourceVolumeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Resource-Volume-Multi (T-180)');

        $io->text(sprintf('Reference: 1 m³ Wasser. Pop-Multi: <info>%.1f m³/Person</info>.', ResourceVolumeConfig::getPopMulti()));
        $io->newLine();

        // Tabelle: alle Resources sortiert nach Volume desc
        $rows = [];
        foreach (ResourceType::cases() as $type) {
            $multi = ResourceVolumeConfig::getMultiForResource($type);
            $rows[] = [$type->value, $type->getCategory()->value, sprintf('%.1f m³', $multi)];
        }
        usort($rows, fn ($a, $b) => (float) $b[2] <=> (float) $a[2]);
        $io->table(['Resource', 'Category', 'm³/Unit'], $rows);

        // Beispiel-Berechnungen
        $io->section('Beispiel-Storage-Berechnungen');
        $examples = [
            ['1000 Iron-Ore', ResourceVolumeConfig::getMultiForResource(ResourceType::IRON_ORE) * 1000],
            ['1000 Water', ResourceVolumeConfig::getMultiForResource(ResourceType::WATER) * 1000],
            ['1000 Oxygen', ResourceVolumeConfig::getMultiForResource(ResourceType::OXYGEN) * 1000],
            ['1000 Iron-Bar', ResourceVolumeConfig::getMultiForResource(ResourceType::IRON_BAR) * 1000],
            ['1000 Uranium-Ore', ResourceVolumeConfig::getMultiForResource(ResourceType::URANIUM_ORE) * 1000],
            ['100 Pop', ResourceVolumeConfig::getPopMulti() * 100],
            ['1000 Pop', ResourceVolumeConfig::getPopMulti() * 1000],
        ];
        $exRows = [];
        foreach ($examples as [$label, $totalM3]) {
            $exRows[] = [$label, sprintf('%s m³', number_format($totalM3, 0, '.', "\u{202F}"))];
        }
        $io->table(['Menge', 'Total-Volumen'], $exRows);

        // Typisches Storage-Profil
        $io->section('Typisches Storage-Profil (frischer Start-Planet)');
        $profile = [
            ['100 Pop', ResourceVolumeConfig::getPopMulti() * 100],
            ['3000 Iron-Ore', ResourceVolumeConfig::getMultiForResource(ResourceType::IRON_ORE) * 3000],
            ['800 Coal', ResourceVolumeConfig::getMultiForResource(ResourceType::COAL) * 800],
            ['1500 Water', ResourceVolumeConfig::getMultiForResource(ResourceType::WATER) * 1500],
            ['1500 Food', ResourceVolumeConfig::getMultiForResource(ResourceType::FOOD) * 1500],
            ['1500 Oxygen', ResourceVolumeConfig::getMultiForResource(ResourceType::OXYGEN) * 1500],
        ];
        $sum = 0.0;
        $profileRows = [];
        foreach ($profile as [$label, $v]) {
            $sum += $v;
            $profileRows[] = [$label, sprintf('%s m³', number_format($v, 0, '.', "\u{202F}"))];
        }
        $profileRows[] = ['---', '---'];
        $profileRows[] = ['<info>Summe</info>', sprintf('<info>%s m³</info>', number_format($sum, 0, '.', "\u{202F}"))];
        $io->table(['Position', 'Volumen'], $profileRows);

        return Command::SUCCESS;
    }
}
