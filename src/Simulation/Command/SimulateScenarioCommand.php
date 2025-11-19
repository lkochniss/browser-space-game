<?php
namespace App\Simulation\Command;

use App\Simulation\Scenario\ScenarioInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:simulate',
    description: 'Runs a simulation scenario'
)]
class SimulateScenarioCommand extends Command
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'scenario',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the scenario class (without namespace)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scenarioName = $input->getOption('scenario');

        if (!$scenarioName) {
            $output->writeln('<error>No scenario specified.</error>');
            return Command::FAILURE;
        }

        $scenarioClass = 'App\\Simulation\\Scenario\\' . $scenarioName;

        if (!class_exists($scenarioClass)) {
            $output->writeln("<error>Scenario {$scenarioClass} does not exist.</error>");
            return Command::FAILURE;
        }

        /** @var ScenarioInterface $scenario */
        $scenario = $this->container->get($scenarioClass);

        $scenario->run();

        $output->writeln("<info>Scenario {$scenarioName} executed.</info>");

        return Command::SUCCESS;
    }
}
