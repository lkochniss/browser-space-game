<?php
namespace App\Simulation\Command;

use App\Simulation\Scenario\IronOreScenario;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:simulate',
    description: 'Runs a simulation scenario'
)]
class SimulateScenarioCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $scenario = new IronOreScenario();
       $scenario->run();

        return Command::SUCCESS;
    }
}
