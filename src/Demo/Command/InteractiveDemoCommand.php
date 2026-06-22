<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Command\UpgradeBuildingCommand;
use App\Building\Model\Building;
use App\Building\Service\BuildBuildingCommandService;
use App\Building\Service\BuildingCostConfig;
use App\Building\Service\BuildingUnlockConfig;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\Demo\Service\DemoActionLogger;
use App\Demo\Service\DemoGoalChecker;
use App\Demo\Service\StateSnapshotter;
use App\Discovery\Repository\PlayerSystemDiscoveryRepository;
use App\Discovery\Service\TelescopeDiscoveryService;
use App\Research\Command\StartResearchCommand;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\Repository\PlayerResearchRepository;
use App\Research\Service\ResearchCompletionService;
use App\Research\Service\ResearchDurationConfig;
use App\Research\Service\ResearchTree;
use App\Faction\Service\FactionSeedService;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Command\DisbandFleetCommand;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\Service\FleetArrivalService;
use App\Fleet\ValueObject\FleetStatus;
use App\GameState\Model\GameState;
use App\POI\Model\AsteroidField;
use App\POI\Model\DebrisField;
use App\POI\Model\Nebula;
use App\POI\Model\SpaceStation;
use App\POI\Model\Wormhole;
use App\POI\Repository\PoiRepository;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Command\ColonizePlanetCommand;
use App\Planet\Repository\PlanetRepository;
use App\Probe\Service\ProbeCostConfig;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use App\Probe\Command\BuildProbeCommand;
use App\Probe\ValueObject\ProbeType;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Command\BuildShipCommand;
use App\Ship\Command\LoadCargoCommand;
use App\Ship\Command\StartSalvageCommand;
use App\Ship\Command\StopSalvageCommand;
use App\Ship\Command\UnloadCargoCommand;
use App\Ship\Repository\ShipRepository;
use App\Ship\Service\SalvageProcessor;
use App\Ship\Service\ShipCostConfig;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\Repository\SolarSystemRepository;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\POI\ValueObject\PoiId;
use App\Resource\Model\Resource;
use App\Tick\Engine\TickEngine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * T-082 Interactive Demo-CLI.
 *
 * Sandbox-Tool zum manuellen Üben aller bisher implementierten Game-Actions.
 * Nutzt eigene SQLite-File `var/demo.db` (via APP_ENV=demo).
 *
 * Aufruf: bin/console app:demo:run --env=demo
 *
 * Mit `--reset`: löscht Schema, neuer Player + Galaxy.
 * Sonst: continue mit existierendem State.
 */
#[AsCommand(
    name: 'app:demo:run',
    description: 'T-082 Interactive Demo Sandbox: alle Actions im CLI-Choice-Menü.',
)]
class InteractiveDemoCommand extends Command
{
    /** @var array<string, mixed> Set per action via setLastActionParams; reset before each loop iteration */
    private array $lastActionParams = [];

    /** T-169: nach Reset-Action gesetzter neuer Player; Main-Loop schwenkt um. */
    private ?Player $pendingPlayerSwap = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdjustableClock $clock,
        private readonly PlayerRepository $playerRepository,
        private readonly PlanetRepository $planetRepository,
        private readonly ShipRepository $shipRepository,
        private readonly FleetRepository $fleetRepository,
        private readonly PoiRepository $poiRepository,
        private readonly SolarSystemRepository $solarSystemRepository,
        private readonly FactionSeedService $factionSeed,
        private readonly CommandBusInterface $bus,
        private readonly TickEngine $tickEngine,
        private readonly FleetArrivalService $fleetArrival,
        private readonly SalvageProcessor $salvageProcessor,
        private readonly BuildingCostConfig $buildingCostConfig,
        private readonly ShipCostConfig $shipCostConfig,
        private readonly ProbeCostConfig $probeCostConfig,
        private readonly DemoGoalChecker $goalChecker,
        private readonly TelescopeDiscoveryService $telescopeDiscovery,
        private readonly PlayerSystemDiscoveryRepository $discoveryRepository,
        private readonly DemoActionLogger $logger,
        private readonly StateSnapshotter $snapshotter,
        private readonly ResearchTree $researchTree,
        private readonly ResearchDurationConfig $researchDurationConfig,
        private readonly \App\Research\Service\StartResearchCommandService $startResearchService,
        private readonly ResearchCompletionService $researchCompletion,
        private readonly PlayerResearchRepository $playerResearchRepository,
        private readonly ActiveResearchRepository $activeResearchRepository,
        private readonly BuildBuildingCommandService $buildService,
        private readonly BuildingUnlockConfig $unlockConfig,
        private readonly \App\Building\Service\BuildQueueCapCalculator $queueCapCalculator,
        private readonly \App\Planet\Service\PlayerPlanetCapCalculator $planetCapCalculator,
        #[Autowire('%kernel.environment%')]
        private readonly string $kernelEnv,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Drop schema + new player');
    }

    /**
     * T-168: Wenn Command aus dev/prod env gestartet wird, in Sub-Prozess mit
     * APP_ENV=demo + --env=demo neu aufrufen. setTty=true reicht stdin/stdout
     * an Child-Prozess durch → Choice-Loop bleibt interaktiv.
     */
    private function reexecInDemoEnv(InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $args = [PHP_BINARY, $this->projectDir . '/bin/console', 'app:demo:run', '--env=demo'];
        if ($input->getOption('reset')) {
            $args[] = '--reset';
        }
        if (!$input->isInteractive()) {
            $args[] = '--no-interaction';
        }

        $io->note(sprintf('Auto-switch %s → demo env (Demo nutzt eigene SQLite-DB var/demo.db).', $this->kernelEnv));

        $process = new Process($args, $this->projectDir, ['APP_ENV' => 'demo']);
        $process->setTimeout(null);

        if (Process::isTtySupported() && $input->isInteractive()) {
            $process->setTty(true);
            $process->run();
        } else {
            // Fallback ohne TTY — Output direkt durchreichen (für --no-interaction
            // smoke-tests + non-tty-CI).
            $process->run(function (string $type, string $buffer) use ($output): void {
                $output->write($buffer);
            });
        }

        return $process->getExitCode() ?? Command::FAILURE;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Browser Space Game — Interactive Demo Sandbox');

        // T-168 Auto-Switch: Demo-Command MUSS in demo-Env laufen (eigene SQLite-DB).
        // Falls anders gestartet → in Sub-Prozess mit korrektem Env neu aufrufen
        // (cleanes Re-Exec via Symfony\Process).
        if ($this->kernelEnv !== 'demo') {
            return $this->reexecInDemoEnv($input, $output, $io);
        }

        $reset = (bool) $input->getOption('reset');
        $player = $this->setupSession($io, $reset);

        if ($player === null) {
            $io->error('Could not initialize demo session.');

            return Command::FAILURE;
        }

        $io->success(sprintf('Demo started for Player %s (Clock: %s)', $player->getId(), $this->clock->now()->format('Y-m-d H:i:s')));

        // Main Menue Loop
        while (true) {
            $action = $io->choice('Action', $this->menuOptions(), 'Status');
            $this->lastActionParams = [];
            $error = null;
            $success = true;

            try {
                $continue = match ($action) {
                    'Status' => $this->showStatus($io, $player),
                    'Goals' => $this->showGoals($io, $player),
                    'Galaxy Overview' => $this->showGalaxy($io, $player),
                    'Export Log' => $this->exportLog($io),
                    'Build Building' => $this->buildBuilding($io, $player),
                    'Upgrade Building' => $this->upgradeBuilding($io, $player),
                    'Cancel Build' => $this->cancelBuild($io, $player),
                    'Build Ship' => $this->buildShip($io, $player),
                    'Build Probe' => $this->buildProbe($io, $player),
                    'Create Fleet' => $this->createFleet($io, $player),
                    'Move Fleet' => $this->moveFleet($io, $player),
                    'Disband Fleet' => $this->disbandFleet($io, $player),
                    'Load Cargo' => $this->loadCargo($io, $player),
                    'Unload Cargo' => $this->unloadCargo($io, $player),
                    'Start Salvage' => $this->startSalvage($io, $player),
                    'Stop Salvage' => $this->stopSalvage($io, $player),
                    'Colonize Planet' => $this->colonizePlanet($io, $player),
                    'Tick Forward (advance time)' => $this->tickForward($io, $player),
                    'Forschung' => $this->doResearch($io, $player),
                    'Set Background' => $this->setBackground($io, $player),
                    'Reset Demo' => $this->resetSession($io, $player),
                    'Quit' => false,
                    default => true,
                };
            } catch (Throwable $e) {
                $io->error(sprintf('Error: %s', $e->getMessage()));
                $continue = true;
                $error = $e->getMessage();
                $success = false;
            }

            // T-169: Wenn Reset-Action neuen Player gebootstrapped hat, schwenken
            // statt den alten (gelöschten) per-ID nachladen — alte ID existiert nicht mehr.
            if ($this->pendingPlayerSwap !== null) {
                $player = $this->pendingPlayerSwap;
                $this->pendingPlayerSwap = null;
            } else {
                // Reload Player after potential mutation/clear
                $player = $this->playerRepository->find($player->getId());
                if ($player === null) {
                    $io->warning('Player no longer exists — exiting.');

                    return Command::SUCCESS;
                }
            }

            // T-082d: Action-Log nach jeder Iteration. Read-only-Actions (Status/Goals/
            // Galaxy/Export Log) werden bewusst auch geloggt — sind günstig + zeigen
            // KI späteren Spielfluss ("User hat zwischen 2 Tick-Advances 3× Status geprüft").
            if ($action !== 'Quit') {
                try {
                    $snapshot = $this->snapshotter->snapshot($player);
                    $this->logger->log($action, $this->lastActionParams, $snapshot, $success, $error);
                } catch (Throwable $logException) {
                    // Log-Failure darf Demo nicht crashen
                    $io->warning(sprintf('Log-write failed: %s', $logException->getMessage()));
                }
            }

            if ($continue === false) {
                break;
            }
        }

        $io->success('Bye.');

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function menuOptions(): array
    {
        return [
            'Status',
            'Goals',
            'Galaxy Overview',
            'Export Log',
            'Build Building',
            'Upgrade Building',
            'Cancel Build',
            'Build Ship',
            'Build Probe',
            'Create Fleet',
            'Move Fleet',
            'Disband Fleet',
            'Load Cargo',
            'Unload Cargo',
            'Start Salvage',
            'Stop Salvage',
            'Colonize Planet',
            'Tick Forward (advance time)',
            'Forschung',
            'Set Background',
            'Reset Demo',
            'Quit',
        ];
    }

    private function setupSession(SymfonyStyle $io, bool $reset): ?Player
    {
        $tool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        // Schema-Existence-Check: probiere PlayerRepository::findAll, fang DBAL-Error.
        $players = [];
        $schemaExists = true;
        try {
            $players = $this->playerRepository->findAll();
        } catch (\Throwable) {
            $schemaExists = false;
        }

        if ($reset || !$schemaExists || count($players) === 0) {
            $io->section('Setup');
            if (!$schemaExists) {
                $io->text('Schema does not exist, creating...');
            } elseif (count($players) > 0) {
                $io->text('Resetting demo state...');
            } else {
                $io->text('First-time setup, creating fresh demo state...');
            }
            // T-082d: existierendes Action-Log nach .bak verschieben damit Vorgeschichte erhalten bleibt
            $backup = $this->logger->backupOnReset();
            if ($backup !== null) {
                $io->text(sprintf('Previous demo-log backed up to %s', basename($backup)));
            }

            return $this->bootstrapFreshPlayer($schemaExists);
        }

        // Resume existing
        $io->note(sprintf('Resuming with existing player %s. Use --reset for fresh state.', $players[0]->getId()));

        return $players[0];
    }

    /**
     * T-169: Zentrale Bootstrap-Routine — wird von setupSession (initial + --reset)
     * und resetSession (Menu-Action) genutzt damit Buff + Galaxy-Garantie konsistent
     * angewendet werden.
     */
    private function bootstrapFreshPlayer(bool $schemaExists): ?Player
    {
        $tool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if ($schemaExists) {
            $tool->dropSchema($metadata);
        }
        $tool->createSchema($metadata);
        $this->factionSeed->seed();

        $playerId = PlayerId::generate();
        $planetId = \App\Planet\ValueObject\PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));

        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            return null;
        }
        $this->applyDemoBuff($player);
        $this->ensureDemoGalaxyContent();

        return $player;
    }

    private function showStatus(SymfonyStyle $io, Player $player): bool
    {
        $io->section(sprintf('Status — Player %s — Clock: %s', $player->getId(), $this->clock->now()->format('Y-m-d H:i:s')));

        $planetCap = $this->planetCapCalculator->compute($player);
        $planetUsed = $this->planetCapCalculator->currentUsage($player);
        $io->text(sprintf('Planets: <info>%d/%d</info>', $planetUsed, $planetCap));

        foreach ($player->getPlanets() as $planet) {
            $sys = $planet->getSolarSystem();
            $io->text(sprintf(
                '<info>Planet</info> %s [%s/%s] in %s',
                $planet->getId(),
                $planet->getType()->value,
                $planet->getSize()->value,
                $sys?->getName() ?? '<no-system>',
            ));

            $pop = $planet->getPopulation();
            $io->text(sprintf('  Pop: %d/%d (assigned %d)', $pop->getTotal(), $pop->getCap(), $pop->getAssigned()));

            if (!$planet->getResources()->isEmpty()) {
                $resourceLines = [];
                foreach ($planet->getResources() as $r) {
                    $resourceLines[] = sprintf('%s=%d', $r->getType()->value, $r->getAmount());
                }
                $io->text('  Resources: ' . implode(', ', $resourceLines));
            }

            if (!$planet->getBuildings()->isEmpty()) {
                $bLines = [];
                foreach ($planet->getBuildings() as $b) {
                    $ready = $b->isReady($this->clock->now()) ? 'ready' : 'building';
                    $bLines[] = sprintf('%s L%d (%s)', $b->getType()->value, $b->getLevel(), $ready);
                }
                $io->text('  Buildings: ' . implode(', ', $bLines));
            }
            // T-094 + T-094c: Bau-Queue Auslastung (Cap dynamisch via HQ-Level)
            $now = $this->clock->now();
            $active = $planet->countActiveBuildJobs($now);
            $io->text(sprintf('  Build-Queue: %d/%d', $active, $this->queueCapCalculator->compute($planet, $player, $now)));
            // T-171: Slot-Auslastung
            $io->text(sprintf('  Building-Slots: %d/%d', $planet->getBuildingSlotsUsed(), $planet->getBuildingSlotCap()));
        }

        // Ships across all planets
        $shipLines = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                $extra = '';
                if ($ship->isSalvaging()) {
                    $extra = sprintf(' [salvaging %s]', $ship->getSalvageResourceType()?->value ?? '?');
                }
                if ($ship->getFleet() !== null) {
                    $extra .= sprintf(' [fleet %s]', $ship->getFleet()->getId());
                }
                $shipLines[] = sprintf('  %s %s (cargo %d/%d)%s', $ship->getType()->value, $ship->getId(), $ship->getCargo()->getTotalUnits(), $ship->getCargoCapacity(), $extra);
            }
        }
        if ($shipLines !== []) {
            $io->text('Ships:');
            foreach ($shipLines as $l) {
                $io->text($l);
            }
        }

        // Fleets
        $fleets = $this->fleetRepository->findAll();
        $playerFleets = array_filter($fleets, fn ($f) => $f->getPlayer()->getId()->equals($player->getId()));
        if ($playerFleets !== []) {
            $io->text('Fleets:');
            foreach ($playerFleets as $f) {
                $statusInfo = $f->getStatus()->value;
                if ($f->isInTransit() && $f->getArrivedAt() !== null) {
                    $statusInfo .= sprintf(' (arrives %s)', $f->getArrivedAt()->format('Y-m-d H:i:s'));
                }
                $io->text(sprintf('  Fleet %s [%s] ships=%d', $f->getId(), $statusInfo, $f->getShips()->count()));
            }
        }

        // POIs in player's systems
        $systemIds = [];
        foreach ($player->getPlanets() as $planet) {
            $sys = $planet->getSolarSystem();
            if ($sys !== null) {
                $systemIds[$sys->getId()->__toString()] = $sys;
            }
        }
        $poiLines = [];
        foreach ($systemIds as $sys) {
            foreach ($this->poiRepository->findBySolarSystem($sys) as $poi) {
                $detail = match (true) {
                    $poi instanceof AsteroidField => sprintf('asteroid contents=%d', $poi->getTotalAmount()),
                    $poi instanceof DebrisField => sprintf('debris total=%d', $poi->getTotalAmount()),
                    $poi instanceof Nebula => sprintf('nebula concealment=%d', $poi->getConcealmentLevel()),
                    $poi instanceof Wormhole => 'wormhole',
                    $poi instanceof SpaceStation => sprintf('station status=%s', $poi->getStatus()->value),
                    default => 'poi',
                };
                $poiLines[] = sprintf('  [%s] %s (%s) — %s', $sys->getName(), $poi->getId(), $detail, $poi->getName() ?? '');
            }
        }
        if ($poiLines !== []) {
            $io->text('POIs:');
            foreach ($poiLines as $l) {
                $io->text($l);
            }
        }

        return true;
    }

    private function exportLog(SymfonyStyle $io): bool
    {
        $io->section('Demo-Action-Log Export');

        $path = $this->logger->getLogPath();
        $count = $this->logger->lineCount();

        $io->text(sprintf('Log-Pfad: <info>%s</info>', $path));
        $io->text(sprintf('Einträge: <info>%d</info>', $count));

        if ($count === 0) {
            $io->note('Noch keine Einträge.');

            return true;
        }

        $tail = $this->logger->readLast(20);
        $io->newLine();
        $io->text(sprintf('Letzte %d Einträge (kompakt):', count($tail)));
        $io->newLine();

        foreach ($tail as $entry) {
            $ok = ($entry['success'] ?? true) ? '<info>OK</info>' : '<error>FAIL</error>';
            $action = $entry['action'] ?? '?';
            $ts = $entry['ts'] ?? '?';
            $params = $entry['params'] ?? [];
            $clock = $entry['snapshot']['clock_now'] ?? '?';
            $paramStr = $params === [] ? '' : ' ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $error = isset($entry['error']) ? sprintf(' <error>err: %s</error>', $entry['error']) : '';
            $io->text(sprintf('  [%s] [clk %s] %s %s%s%s', $ts, $clock, $ok, $action, $paramStr, $error));
        }

        $io->newLine();
        $io->note('Vollen Log via `cat ' . $path . '` oder direkt an die KI für Tuning kopieren.');

        return true;
    }

    private function showGoals(SymfonyStyle $io, Player $player): bool
    {
        $io->section('Demo-Goals');

        $goals = $this->goalChecker->check($player);
        $done = 0;
        foreach ($goals as $g) {
            $marker = $g->completed ? '<info>✓</info>' : '<comment>✗</comment>';
            if ($g->completed) {
                $done++;
            }
            $io->text(sprintf('  %s %s — %s', $marker, $g->label, $g->progressHint));
        }
        $io->newLine();
        $io->text(sprintf('Progress: <info>%d/%d</info> Goals erledigt.', $done, count($goals)));

        return true;
    }

    private function showGalaxy(SymfonyStyle $io, Player $player): bool
    {
        $io->section('Galaxy Overview');

        $allSystems = $this->solarSystemRepository->findAll();
        if ($allSystems === []) {
            $io->note('No solar systems in galaxy.');

            return true;
        }

        // T-018: nur entdeckte Systeme zeigen
        $discoveries = $this->discoveryRepository->findByPlayer($player);
        $discoveredIds = [];
        foreach ($discoveries as $d) {
            $discoveredIds[$d->getSolarSystem()->getId()->__toString()] = true;
        }

        $systems = array_filter(
            $allSystems,
            fn ($s) => isset($discoveredIds[$s->getId()->__toString()]),
        );
        $undiscoveredCount = count($allSystems) - count($systems);

        if ($systems === []) {
            $io->note('No discovered systems yet — build a Telescope to scan the galaxy.');

            return true;
        }

        if ($undiscoveredCount > 0) {
            $io->text(sprintf('<comment>%d unbekannte System(e)</comment> — Teleskop bauen für mehr Sicht.', $undiscoveredCount));
            $io->newLine();
        }

        foreach ($systems as $system) {
            $planets = $system->getPlanets()->toArray();
            $pois = $this->poiRepository->findBySolarSystem($system);

            $io->text(sprintf('<info>System</info> %s [%s]', $system->getName(), $system->getId()));

            // Planets
            foreach ($planets as $planet) {
                $owner = $planet->getPlayer();
                $ownerLabel = $owner === null
                    ? '<comment>unclaimed</comment>'
                    : ($owner->getId()->equals($player->getId()) ? '<info>you</info>' : 'other');
                $io->text(sprintf(
                    '  Planet %s [%s/%s] — %s',
                    $planet->getId(),
                    $planet->getType()->value,
                    $planet->getSize()->value,
                    $ownerLabel,
                ));
            }

            // POIs
            foreach ($pois as $poi) {
                $detail = match (true) {
                    $poi instanceof AsteroidField => sprintf('asteroid total=%d', $poi->getTotalAmount()),
                    $poi instanceof DebrisField => sprintf('debris total=%d', $poi->getTotalAmount()),
                    $poi instanceof Nebula => sprintf('nebula concealment=%d', $poi->getConcealmentLevel()),
                    $poi instanceof Wormhole => sprintf('wormhole twin=%s', $poi->getTwin()?->getId() ?? '?'),
                    $poi instanceof SpaceStation => sprintf('station status=%s', $poi->getStatus()->value),
                    default => 'poi',
                };
                $io->text(sprintf('  POI %s — %s', $poi->getId(), $detail));
            }

            $io->newLine();
        }

        return true;
    }

    private function buildBuilding(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        $choices = [];
        foreach (BuildingType::cases() as $bt) {
            try {
                $cost = $this->buildingCostConfig->getCost($bt, currentLevel: 0);
                $costParts = [];
                foreach ($cost->resources as $rType => $amount) {
                    $costParts[] = sprintf('%d %s', $amount, $rType);
                }
                $costParts[] = sprintf('%d pop', $cost->populationCost);
                $costStr = implode(', ', $costParts);
            } catch (\Throwable) {
                $costStr = 'no cost configured';
            }

            // T-170: Tech-Tree-Lock anzeigen
            $unlock = $this->unlockConfig->requiredResearch($bt);
            if ($unlock !== null && !$this->buildService->isUnlockedFor($player, $bt)) {
                $choices[$bt->value] = sprintf(
                    '🔒 %s (%s) — erfordert %s L%d',
                    $bt->value,
                    $costStr,
                    $unlock['slug'],
                    $unlock['level'],
                );
            } elseif ($bt->isUnique() && $planet->hasBuildingOfType($bt)) {
                // T-171: Unique bereits gebaut → Hint auf Upgrade
                $choices[$bt->value] = sprintf(
                    '✓ %s (unique, gebaut — verwende Upgrade)',
                    $bt->value,
                );
            } else {
                $sizeNote = $bt->getSlotSize() > 1 ? sprintf(', %d slots', $bt->getSlotSize()) : '';
                $choices[$bt->value] = sprintf('%s (%s%s)', $bt->value, $costStr, $sizeNote);
            }
        }
        $type = $io->choice('Building Type', $choices);
        // $type ist hier der Display-Wert; finde den enum-Key zurück
        $enumValue = array_search($type, $choices, true);
        if ($enumValue === false) {
            $enumValue = $type;
        }
        $this->lastActionParams = [
            'planet_id' => (string) $planet->getId(),
            'building_type' => (string) $enumValue,
        ];
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::from((string) $enumValue)));
        $io->success(sprintf('Build started: %s on %s', $enumValue, $planet->getId()));

        return true;
    }

    private function cancelBuild(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        $now = $this->clock->now();
        $unfinished = [];
        foreach ($planet->getBuildings() as $b) {
            if (!$b->isReady($now)) {
                $unfinished[] = $b;
            }
        }
        if ($unfinished === []) {
            $io->note('Keine laufenden Bauten/Upgrades auf diesem Planeten.');

            return true;
        }

        $choices = [];
        foreach ($unfinished as $i => $b) {
            $kind = $b->getLevel() === 1 ? 'Build' : sprintf('Upgrade L%d→L%d', $b->getLevel() - 1, $b->getLevel());
            $choices[$i] = sprintf('%s %s (id=%s)', $b->getType()->value, $kind, $b->getId());
        }
        $idx = $io->choice('Bau zum Canceln', $choices);
        $idxNum = array_search($idx, $choices, true);
        $building = $unfinished[$idxNum] ?? null;
        if ($building === null) {
            $io->note('Invalid selection.');

            return true;
        }

        $this->lastActionParams = [
            'planet_id' => (string) $planet->getId(),
            'building_id' => (string) $building->getId(),
            'building_type' => $building->getType()->value,
            'level_at_cancel' => $building->getLevel(),
        ];
        $this->bus->dispatch(new \App\Building\Command\CancelBuildCommand($planet->getId(), $building->getId()));
        $io->success(sprintf('Cancel: %s — 50%% Refund + Pop frei.', $building->getType()->value));

        return true;
    }

    private function upgradeBuilding(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        $buildings = $planet->getBuildings()->toArray();
        if ($buildings === []) {
            $io->note('No buildings on this planet.');

            return true;
        }

        $choices = [];
        foreach ($buildings as $i => $b) {
            $choices[$i] = sprintf('%s L%d (id=%s)', $b->getType()->value, $b->getLevel(), $b->getId());
        }
        $idx = $io->choice('Building to upgrade', $choices);
        $idxNum = array_search($idx, $choices, true);
        $building = $buildings[$idxNum] ?? null;
        if ($building === null) {
            $io->note('Invalid selection.');

            return true;
        }

        $this->lastActionParams = [
            'planet_id' => (string) $planet->getId(),
            'building_id' => (string) $building->getId(),
            'building_type' => $building->getType()->value,
            'from_level' => $building->getLevel(),
        ];
        $this->bus->dispatch(new UpgradeBuildingCommand($planet->getId(), $building->getId()));
        $io->success(sprintf('Upgrade started: %s', $building->getType()->value));

        return true;
    }

    private function buildShip(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        if (!$planet->hasShipyard($this->clock->now())) {
            $io->warning('Dieser Planet hat keine fertige Raumwerft (SHIPYARD). Build eine zuerst.');

            return true;
        }

        $choices = [];
        foreach (ShipType::cases() as $st) {
            $resources = $this->shipCostConfig->getResourceCost($st);
            $costParts = [];
            foreach ($resources as $rType => $amount) {
                $costParts[] = sprintf('%d %s', $amount, $rType);
            }
            $costParts[] = sprintf('%d pop', $this->shipCostConfig->getPopulationCost($st));
            $costParts[] = sprintf('%dmin', (int) round($this->shipCostConfig->getDurationSeconds($st) / 60));
            $choices[$st->value] = sprintf('%s (%s, cargo %d)', $st->value, implode(', ', $costParts), $this->shipCostConfig->getCargoCapacity($st));
        }
        $label = $io->choice('Ship Type', $choices);
        $enumValue = array_search($label, $choices, true);
        if ($enumValue === false) {
            $enumValue = $label;
        }
        $this->lastActionParams = [
            'planet_id' => (string) $planet->getId(),
            'ship_type' => (string) $enumValue,
        ];
        $ship = $this->bus->dispatch(new BuildShipCommand($planet->getId(), ShipType::from((string) $enumValue)));
        $io->success(sprintf('Building Ship %s (%s) — finishedAt %s', $ship->getId(), $enumValue, $ship->getFinishedAt()?->format('H:i:s') ?? '—'));

        return true;
    }

    private function buildProbe(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        if (!$planet->hasProbeLab($this->clock->now())) {
            $io->warning('Dieser Planet hat kein fertiges Probe-Lab. Build eines zuerst.');

            return true;
        }

        $choices = [];
        foreach (ProbeType::cases() as $pt) {
            $resources = $this->probeCostConfig->getResourceCost($pt);
            $costParts = [];
            foreach ($resources as $rType => $amount) {
                $costParts[] = sprintf('%d %s', $amount, $rType);
            }
            $costParts[] = sprintf('%dmin', (int) round($this->probeCostConfig->getDurationSeconds($pt) / 60));
            $choices[$pt->value] = sprintf('%s (%s)', $pt->value, implode(', ', $costParts));
        }
        $label = $io->choice('Probe Type', $choices);
        $enumValue = array_search($label, $choices, true);
        if ($enumValue === false) {
            $enumValue = $label;
        }
        $this->lastActionParams = [
            'planet_id' => (string) $planet->getId(),
            'probe_type' => (string) $enumValue,
        ];
        $probe = $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::from((string) $enumValue)));
        $io->success(sprintf('Building Probe %s (%s)', $probe->getId(), $enumValue));

        return true;
    }

    private function createFleet(SymfonyStyle $io, Player $player): bool
    {
        // Sammeln aller eligible Ships (ready, kein Fleet, gleicher Planet)
        $ships = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                if ($ship->getFleet() === null && $ship->isReady($this->clock->now())) {
                    $ships[] = $ship;
                }
            }
        }
        if ($ships === []) {
            $io->note('No eligible ships (need ready + no existing fleet).');

            return true;
        }

        $choices = [];
        foreach ($ships as $s) {
            $choices[$s->getId()->__toString()] = sprintf('%s (%s) on %s', $s->getId(), $s->getType()->value, $s->getPlanet()?->getId());
        }
        $selected = $io->choice('Ships (comma-separate values)', $choices, null);
        // Symfony-Choice gibt einen einzelnen Wert. Wir wrap to array für Multi-Ship-Fleet.
        $shipIds = [new \App\Ship\ValueObject\ShipId($selected)];

        $fleet = $this->bus->dispatch(new CreateFleetCommand($player->getId(), $shipIds));
        $this->lastActionParams = [
            'fleet_id' => (string) $fleet->getId(),
            'ship_ids' => array_map(fn ($id) => (string) $id, $shipIds),
            'origin_planet_id' => (string) $fleet->getOriginPlanet()?->getId(),
        ];
        $io->success(sprintf('Fleet %s created at %s', $fleet->getId(), $fleet->getOriginPlanet()?->getId()));

        return true;
    }

    private function moveFleet(SymfonyStyle $io, Player $player): bool
    {
        $fleet = $this->chooseDockedFleet($io, $player);
        if ($fleet === null) {
            return true;
        }

        $allPlanets = $this->planetRepository->findAll();
        $choices = [];
        foreach ($allPlanets as $p) {
            $sys = $p->getSolarSystem();
            $choices[$p->getId()->__toString()] = sprintf('%s (%s)', $p->getId(), $sys?->getName() ?? '?');
        }
        $targetId = $io->choice('Target Planet', $choices);
        $this->lastActionParams = [
            'fleet_id' => (string) $fleet->getId(),
            'target_planet_id' => $targetId,
        ];
        $this->bus->dispatch(new MoveFleetCommand($fleet->getId(), new \App\Planet\ValueObject\PlanetId($targetId)));
        $io->success(sprintf('Fleet %s moving — arrives %s', $fleet->getId(), $fleet->getArrivedAt()?->format('Y-m-d H:i:s')));

        return true;
    }

    private function disbandFleet(SymfonyStyle $io, Player $player): bool
    {
        $fleet = $this->chooseDockedFleet($io, $player);
        if ($fleet === null) {
            return true;
        }
        $this->lastActionParams = ['fleet_id' => (string) $fleet->getId()];
        $this->bus->dispatch(new DisbandFleetCommand($fleet->getId()));
        $io->success('Fleet disbanded.');

        return true;
    }

    private function loadCargo(SymfonyStyle $io, Player $player): bool
    {
        $ship = $this->chooseTransportShip($io, $player);
        if ($ship === null) {
            return true;
        }
        $resourceVal = $io->choice('Resource', array_map(fn ($c) => $c->value, ResourceType::cases()));
        $amount = (int) $io->ask('Amount', '100');
        $this->lastActionParams = [
            'ship_id' => (string) $ship->getId(),
            'resources' => [$resourceVal => $amount],
        ];
        $this->bus->dispatch(new LoadCargoCommand(
            shipId: $ship->getId(),
            resources: [$resourceVal => $amount],
        ));
        $io->success(sprintf('Loaded %d %s into %s', $amount, $resourceVal, $ship->getId()));

        return true;
    }

    private function unloadCargo(SymfonyStyle $io, Player $player): bool
    {
        $ship = $this->chooseTransportShip($io, $player);
        if ($ship === null) {
            return true;
        }
        $contents = $ship->getCargo()->getResources();
        if ($contents === []) {
            $io->note('Cargo empty.');

            return true;
        }
        $resourceVal = $io->choice('Resource', array_keys($contents));
        $amount = (int) $io->ask('Amount', (string) $contents[$resourceVal]);
        $this->lastActionParams = [
            'ship_id' => (string) $ship->getId(),
            'resources' => [$resourceVal => $amount],
        ];
        $this->bus->dispatch(new UnloadCargoCommand(
            shipId: $ship->getId(),
            resources: [$resourceVal => $amount],
        ));
        $io->success(sprintf('Unloaded %d %s', $amount, $resourceVal));

        return true;
    }

    private function startSalvage(SymfonyStyle $io, Player $player): bool
    {
        $salvageShip = $this->chooseSalvageShip($io, $player);
        if ($salvageShip === null) {
            return true;
        }

        // POIs im selben System wie Ship
        $sys = $salvageShip->getPlanet()?->getSolarSystem();
        if ($sys === null) {
            $io->note('Ship not docked at a planet with system.');

            return true;
        }
        $pois = $this->poiRepository->findBySolarSystem($sys);
        $fields = array_filter($pois, fn ($p) => $p instanceof \App\POI\Model\SalvageableField);
        if ($fields === []) {
            $io->note('No salvageable fields (asteroid/debris) in this system.');

            return true;
        }

        $choices = [];
        foreach ($fields as $f) {
            $kind = $f instanceof DebrisField ? 'debris' : 'asteroid';
            $choices[$f->getId()->__toString()] = sprintf('%s [%s] (contents %d)', $f->getId(), $kind, $f->getTotalAmount());
        }
        $poiIdStr = $io->choice('Salvage Target', $choices);

        /** @var \App\POI\Model\SalvageableField $field */
        $field = $this->poiRepository->find(new \App\POI\ValueObject\PoiId($poiIdStr));
        $resourceChoices = [];
        foreach ($field->getContents() as $resVal => $amount) {
            $resourceChoices[$resVal] = sprintf('%s (%d)', $resVal, $amount);
        }
        $resVal = $io->choice('Resource to extract', $resourceChoices);

        $this->lastActionParams = [
            'ship_id' => (string) $salvageShip->getId(),
            'poi_id' => $poiIdStr,
            'resource_type' => $resVal,
        ];
        $this->bus->dispatch(new StartSalvageCommand(
            shipId: $salvageShip->getId(),
            poiId: new \App\POI\ValueObject\PoiId($poiIdStr),
            resourceType: ResourceType::from($resVal),
        ));
        $io->success(sprintf('Salvage started — %s extracts %s @ 50/min', $salvageShip->getId(), $resVal));

        return true;
    }

    private function stopSalvage(SymfonyStyle $io, Player $player): bool
    {
        $salvageShip = $this->chooseSalvageShip($io, $player, requireActive: true);
        if ($salvageShip === null) {
            return true;
        }
        $this->lastActionParams = ['ship_id' => (string) $salvageShip->getId()];
        $this->bus->dispatch(new StopSalvageCommand($salvageShip->getId()));
        $io->success('Salvage stopped.');

        return true;
    }

    private function colonizePlanet(SymfonyStyle $io, Player $player): bool
    {
        // Find Colony-Ships
        $colonyShips = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                if ($ship->getType() === ShipType::COLONY_SHIP && $ship->isReady($this->clock->now())) {
                    $colonyShips[] = $ship;
                }
            }
        }
        if ($colonyShips === []) {
            $io->note('No ready Colony-Ships.');

            return true;
        }

        $shipChoices = [];
        foreach ($colonyShips as $s) {
            $shipChoices[$s->getId()->__toString()] = sprintf('%s (on %s)', $s->getId(), $s->getPlanet()?->getId());
        }
        $shipIdStr = $io->choice('Colony-Ship', $shipChoices);

        // Unclaimed Planets
        $allPlanets = $this->planetRepository->findAll();
        $unclaimed = array_filter($allPlanets, fn ($p) => $p->getPlayer() === null);
        if ($unclaimed === []) {
            $io->note('No unclaimed planets in galaxy.');

            return true;
        }

        $targetChoices = [];
        foreach ($unclaimed as $p) {
            $targetChoices[$p->getId()->__toString()] = sprintf('%s (%s)', $p->getId(), $p->getSolarSystem()?->getName() ?? '?');
        }
        $targetIdStr = $io->choice('Target Planet', $targetChoices);

        $this->lastActionParams = [
            'ship_id' => $shipIdStr,
            'target_planet_id' => $targetIdStr,
        ];
        $this->bus->dispatch(new ColonizePlanetCommand(
            shipId: new \App\Ship\ValueObject\ShipId($shipIdStr),
            targetPlanetId: new \App\Planet\ValueObject\PlanetId($targetIdStr),
        ));
        $io->success(sprintf('Colonized %s.', $targetIdStr));

        return true;
    }

    private function tickForward(SymfonyStyle $io, Player $player): bool
    {
        $advanceChoice = $io->choice('Advance Clock by', ['+15min', '+1h', '+4h', '+1d', 'custom seconds']);
        $seconds = match ($advanceChoice) {
            '+15min' => 900,
            '+1h' => 3600,
            '+4h' => 14400,
            '+1d' => 86400,
            'custom seconds' => (int) $io->ask('Seconds', '900'),
            default => 0,
        };
        $this->clock->advanceSeconds($seconds);

        // Resolve Tick
        $gs = new GameState(player: $player, clock: $this->clock);
        $this->tickEngine->run($gs);
        $arrived = $this->fleetArrival->resolveArrivedFleets();
        $salvaged = $this->salvageProcessor->runTick();
        $discovered = $this->telescopeDiscovery->runTickForPlayer($player);
        $researchDone = $this->researchCompletion->runTickForPlayer($player);

        $this->lastActionParams = [
            'advance_seconds' => $seconds,
            'fleets_arrived' => $arrived,
            'salvages_processed' => $salvaged,
            'systems_discovered' => $discovered,
            'research_completed' => $researchDone,
        ];

        $io->success(sprintf(
            'Tick advanced by %ds — Clock: %s | Fleets arrived: %d | Salvages: %d | Discovered: %d | Research-done: %d',
            $seconds,
            $this->clock->now()->format('Y-m-d H:i:s'),
            $arrived,
            $salvaged,
            $discovered,
            $researchDone,
        ));

        return true;
    }

    private function doResearch(SymfonyStyle $io, Player $player): bool
    {
        $io->section('Forschung');

        // Aktive Forschung anzeigen
        $active = $this->activeResearchRepository->findActiveForPlayer($player);
        $now = $this->clock->now();
        if ($active !== null) {
            $remaining = max(0, $active->getFinishedAt()->getTimestamp() - $now->getTimestamp());
            $io->text(sprintf(
                'Aktiv: <info>%s</info> Level %d → finished_at %s (in %ds)',
                $active->getNodeSlug(),
                $active->getTargetLevel(),
                $active->getFinishedAt()->format('Y-m-d H:i:s'),
                $remaining,
            ));
            $io->newLine();
        }

        // Bisherige Levels anzeigen
        $known = $this->playerResearchRepository->findByPlayer($player);
        if ($known !== []) {
            $io->text('Bereits erforscht:');
            foreach ($known as $r) {
                $io->text(sprintf('  %s — Level %d', $r->getNodeSlug(), $r->getLevel()));
            }
            $io->newLine();
        }

        // T-025c Multi-Lab Opt-In — Player wählt Primary + Booster
        $labs = $this->startResearchService->listReadyLabs($player, $now);
        if ($labs === []) {
            $io->note('Kein fertiges RESEARCH_LAB — bauen, dann zurückkommen.');

            return true;
        }

        // Schritt 1: Primary-Lab
        $primaryChoices = [];
        foreach ($labs as $entry) {
            $key = (string) $entry['planet']->getId();
            $primaryChoices[$key] = sprintf(
                '%s [%s] (Lab L%d)',
                substr((string) $entry['planet']->getId(), 0, 8),
                $entry['planet']->getType()->value,
                $entry['labLevel'],
            );
        }
        $primaryKey = $io->choice('Primary Research-Lab', $primaryChoices, array_key_first($primaryChoices));
        $primaryKeyResolved = array_search($primaryKey, $primaryChoices, true);
        if ($primaryKeyResolved !== false) {
            $primaryKey = $primaryKeyResolved;
        }
        $primaryEntry = null;
        foreach ($labs as $entry) {
            if ((string) $entry['planet']->getId() === $primaryKey) {
                $primaryEntry = $entry;
                break;
            }
        }
        if ($primaryEntry === null) {
            $io->error('Primary-Lab-Auswahl konnte nicht aufgelöst werden.');

            return true;
        }
        $primaryPlanetId = $primaryEntry['planet']->getId();
        $primaryLvl = $primaryEntry['labLevel'];

        // Schritt 2: Booster-Labs (nur wenn weitere Labs verfügbar)
        $boosterIds = [];
        $boosterLvls = [];
        $remainingLabs = array_filter(
            $labs,
            fn (array $entry): bool => (string) $entry['planet']->getId() !== $primaryKey,
        );
        if ($remainingLabs !== []) {
            $boosterChoices = [];
            foreach ($remainingLabs as $entry) {
                $key = (string) $entry['planet']->getId();
                $boosterChoices[$key] = sprintf(
                '%s [%s] (Lab L%d)',
                substr((string) $entry['planet']->getId(), 0, 8),
                $entry['planet']->getType()->value,
                $entry['labLevel'],
            );
            }
            $question = new \Symfony\Component\Console\Question\ChoiceQuestion(
                'Booster-Labs (Komma-getrennt, leer = keine)',
                $boosterChoices,
                '',
            );
            $question->setMultiselect(true);
            $question->setValidator(static fn ($v) => $v ?? '');
            $selected = $io->askQuestion($question);
            if (is_array($selected)) {
                foreach ($selected as $sel) {
                    if ($sel === '' || $sel === null) {
                        continue;
                    }
                    $matchedKey = array_search($sel, $boosterChoices, true);
                    if ($matchedKey === false) {
                        continue;
                    }
                    $boosterKey = (string) $matchedKey;
                    foreach ($remainingLabs as $entry) {
                        if ((string) $entry['planet']->getId() === $boosterKey) {
                            $boosterIds[] = $entry['planet']->getId();
                            $boosterLvls[] = $entry['labLevel'];
                            break;
                        }
                    }
                }
            }
        }

        $effectiveLab = $this->researchTree->computeEffectiveLabLevel($primaryLvl, $boosterLvls);
        $io->text(sprintf(
            'Primary L%d + %d Booster → effective Lab-Level <info>%.3f</info>',
            $primaryLvl,
            count($boosterLvls),
            $effectiveLab,
        ));
        $io->newLine();

        // Schritt 3: Node-Auswahl mit Cost-Preview
        $choices = [];
        foreach ($this->researchTree->all() as $node) {
            $current = $this->playerResearchRepository->findOneByPlayerAndSlug($player, $node->slug);
            $currentLevel = $current?->getLevel() ?? 0;

            if ($currentLevel >= $node->maxLevel) {
                $choices[$node->slug] = sprintf('%s [MAX %d]', $node->slug, $node->maxLevel);
                continue;
            }
            $targetLevel = $currentLevel + 1;
            $cost = $this->researchDurationConfig->resourceCost($node, $targetLevel, $primaryLvl, $boosterLvls);
            $duration = $this->researchDurationConfig->durationSeconds($node, $targetLevel, $effectiveLab);
            $costParts = [];
            foreach ($cost as $resVal => $amount) {
                $costParts[] = sprintf('%d %s', $amount, $resVal);
            }
            $costParts[] = sprintf('%dmin', (int) round($duration / 60));
            $choices[$node->slug] = sprintf('%s L%d (%s)', $node->slug, $targetLevel, implode(', ', $costParts));
        }
        if ($choices === []) {
            $io->note('Keine Nodes im Tree verfügbar.');

            return true;
        }

        $label = $io->choice('Forschung starten', $choices);
        $slug = array_search($label, $choices, true);
        if ($slug === false) {
            $slug = $label;
        }

        $this->lastActionParams = [
            'node_slug' => $slug,
            'primary_planet_id' => (string) $primaryPlanetId,
            'booster_planet_ids' => array_map(static fn ($id) => (string) $id, $boosterIds),
        ];

        $this->bus->dispatch(new StartResearchCommand(
            $player->getId(),
            (string) $slug,
            $primaryPlanetId,
            $boosterIds,
        ));
        $io->success(sprintf('Forschung gestartet: %s', $slug));

        return true;
    }

    private function setBackground(SymfonyStyle $io, Player $player): bool
    {
        $io->section('Player-Background wählen (PERMANENT)');

        $current = $player->getBackground();
        if ($current !== null) {
            $io->warning(sprintf(
                'Background ist bereits gesetzt: %s (%s). Re-Spec ist nicht möglich.',
                $current->getDisplayName(),
                $current->getDescription(),
            ));
            $this->lastActionParams = ['already_set' => $current->value];

            return true;
        }

        $choices = [];
        foreach (\App\Player\ValueObject\PlayerBackground::cases() as $bg) {
            $choices[$bg->value] = sprintf('%s — %s', $bg->getDisplayName(), $bg->getDescription());
        }
        $io->note('Hinweis: Effekte (T-122b) noch nicht aktiv — diese Foundation speichert nur die Wahl.');
        $label = $io->choice('Background', $choices);

        $slug = array_search($label, $choices, true);
        if ($slug === false) {
            $slug = $label;
        }
        $background = \App\Player\ValueObject\PlayerBackground::from((string) $slug);

        if (!$io->confirm(sprintf('PERMANENT setzen: %s?', $background->getDisplayName()), false)) {
            $this->lastActionParams = ['confirmed' => false, 'background' => $background->value];

            return true;
        }

        $this->bus->dispatch(new \App\Player\Command\SetPlayerBackgroundCommand(
            $player->getId(),
            $background,
        ));
        $this->lastActionParams = ['confirmed' => true, 'background' => $background->value];
        $io->success(sprintf('Background gesetzt: %s', $background->getDisplayName()));

        return true;
    }

    private function resetSession(SymfonyStyle $io, Player $player): bool
    {
        if (!$io->confirm('Wirklich alles löschen und frischen Player anlegen?', false)) {
            $this->lastActionParams = ['confirmed' => false];

            return true;
        }
        $backup = $this->logger->backupOnReset();
        if ($backup !== null) {
            $io->text(sprintf('Previous demo-log backed up to %s', basename($backup)));
        }
        $newPlayer = $this->bootstrapFreshPlayer(schemaExists: true);
        if ($newPlayer === null) {
            $io->error('Reset failed — no new player could be bootstrapped.');

            return false;
        }
        // T-169: signalisiere Main-Loop, dass der referenzierte $player ungültig
        // ist und durch den frisch erzeugten ersetzt werden muss.
        $this->pendingPlayerSwap = $newPlayer;
        $this->lastActionParams = [
            'confirmed' => true,
            'new_player_id' => (string) $newPlayer->getId(),
        ];

        $io->success(sprintf('Demo state reset. Neuer Player %s.', $newPlayer->getId()));

        return true;
    }

    /**
     * T-082b/T-082e: Start-Planet bekommt Hub L1 + großzügigen Resource-Buff.
     *
     * Day-1 soll flüssig spielbar sein: Tier-0-Bauten + erste Forschung möglich
     * ohne Mining-Tick abzuwarten + ohne Wasser/Nahrung-Sorge. Long-term-Survival
     * via T-097a Renewable-Producer.
     */
    private function applyDemoBuff(Player $player): void
    {
        $startPlanet = $player->getPlanets()->first();
        if ($startPlanet === false) {
            return;
        }

        $now = $this->clock->now();
        // T-172: HQ wird via ClaimStartPlanet auto-built. Demo-Buff legt zusätzlich
        // 1 HUB L1 als Wohnsiedlung an → Pop-Cap-Boost für Day-1.
        $hub = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $hub->setFinishedAt($now);
        $startPlanet->addBuilding($hub, $now);

        // T-082e Resource-Buff für Day-1-Komfort:
        //   IRON_ORE   3000 — Tier-0-Bauten + Upgrades + Forschung
        //   COAL        800 — HUB/Forschung-Cost-Buffer
        //   COPPER_ORE  400 — RESEARCH_LAB + astronomy/shipbuilding/recycling
        //   SILICON     300 — RESEARCH_LAB + ftl_tier_1
        //   IRON_BAR    200 — shipbuilding-Forschung-Cost-Anteil
        //   W/F/O      1500 — ~30 Ticks Buffer bei 50 Pop, deckt bis Producer steht
        $boosts = [
            ResourceType::IRON_ORE->value => 3000,
            ResourceType::COAL->value => 800,
            ResourceType::COPPER_ORE->value => 400,
            ResourceType::SILICON->value => 300,
            ResourceType::IRON_BAR->value => 200,
            ResourceType::WATER->value => 1500,
            ResourceType::FOOD->value => 1500,
            ResourceType::OXYGEN->value => 1500,
        ];
        foreach ($boosts as $resVal => $amount) {
            $type = ResourceType::from($resVal);
            try {
                $startPlanet->getResource($type)->setAmount($amount);
            } catch (\Throwable) {
                $startPlanet->ensureResource($type)->setAmount($amount);
            }
        }

        $this->em->flush();
    }

    /**
     * T-082b: garantiert mindestens 1 AsteroidField + 1 Wormhole-Pair in der
     * Galaxy (für Salvage- und Travel-Demo). Falls Galaxy-Init bereits welche
     * generiert hat, no-op.
     */
    private function ensureDemoGalaxyContent(): void
    {
        $systems = $this->solarSystemRepository->findAll();
        if (count($systems) < 2) {
            return;
        }

        $hasAsteroid = false;
        $hasWormhole = false;
        $hasDebris = false;
        foreach ($this->poiRepository->findAll() as $poi) {
            if ($poi instanceof AsteroidField) {
                $hasAsteroid = true;
            }
            if ($poi instanceof Wormhole) {
                $hasWormhole = true;
            }
            if ($poi instanceof DebrisField) {
                $hasDebris = true;
            }
        }

        if (!$hasAsteroid) {
            // Asteroid auf erstem System (= Heimat-System)
            $asteroid = new AsteroidField(
                id: PoiId::generate(),
                solarSystem: $systems[0],
                name: 'Demo Asteroid Belt',
                contents: [
                    ResourceType::IRON_ORE->value => 2000,
                    ResourceType::COAL->value => 1000,
                ],
            );
            $systems[0]->addPoi($asteroid);
            $this->em->persist($asteroid);
        }

        if (!$hasDebris) {
            // T-021: kleines DebrisField im Heim-System für Recycling-Demo
            $debris = new DebrisField(
                id: PoiId::generate(),
                solarSystem: $systems[0],
                name: 'Demo Schlachtfeld',
                contents: [
                    ResourceType::DEBRIS_LOW->value => 6,
                    ResourceType::DEBRIS_MEDIUM->value => 3,
                ],
            );
            $systems[0]->addPoi($debris);
            $this->em->persist($debris);
        }

        if (!$hasWormhole) {
            // Wormhole-Pair zwischen System 0 und letztem System
            $sysA = $systems[0];
            $sysB = $systems[count($systems) - 1];
            $whA = new Wormhole(
                id: PoiId::generate(),
                solarSystem: $sysA,
                name: sprintf('Wurmloch %s ↔ %s', $sysA->getName(), $sysB->getName()),
                requiredTechSlug: 'ftl_warp',
            );
            $whB = new Wormhole(
                id: PoiId::generate(),
                solarSystem: $sysB,
                name: sprintf('Wurmloch %s ↔ %s', $sysB->getName(), $sysA->getName()),
                requiredTechSlug: 'ftl_warp',
            );
            $whA->pairWith($whB);
            $sysA->addPoi($whA);
            $sysB->addPoi($whB);
            $this->em->persist($whA);
            $this->em->persist($whB);
        }

        $this->em->flush();
    }

    private function choosePlayerPlanet(SymfonyStyle $io, Player $player): ?\App\Planet\Model\Planet
    {
        $planets = $player->getPlanets()->toArray();
        if ($planets === []) {
            $io->note('No planets — reset demo.');

            return null;
        }
        if (count($planets) === 1) {
            return $planets[0];
        }
        $choices = [];
        foreach ($planets as $p) {
            $choices[$p->getId()->__toString()] = sprintf('%s (%s)', $p->getId(), $p->getSolarSystem()?->getName() ?? '?');
        }
        $idStr = $io->choice('Planet', $choices);

        return $this->planetRepository->find(new \App\Planet\ValueObject\PlanetId($idStr));
    }

    private function chooseDockedFleet(SymfonyStyle $io, Player $player): ?\App\Fleet\Model\Fleet
    {
        $fleets = array_filter(
            $this->fleetRepository->findAll(),
            fn ($f) => $f->getPlayer()->getId()->equals($player->getId()) && $f->getStatus() === FleetStatus::DOCKED,
        );
        if ($fleets === []) {
            $io->note('No docked fleets.');

            return null;
        }
        $choices = [];
        foreach ($fleets as $f) {
            $choices[$f->getId()->__toString()] = sprintf('%s (ships=%d)', $f->getId(), $f->getShips()->count());
        }
        $idStr = $io->choice('Fleet', $choices);

        return $this->fleetRepository->find(new \App\Fleet\ValueObject\FleetId($idStr));
    }

    private function chooseTransportShip(SymfonyStyle $io, Player $player): ?\App\Ship\Model\Ship
    {
        $ships = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                if ($ship->getType()->isTransport() && $ship->isReady($this->clock->now())) {
                    $ships[] = $ship;
                }
            }
        }
        if ($ships === []) {
            $io->note('No ready transport ships.');

            return null;
        }
        $choices = [];
        foreach ($ships as $s) {
            $choices[$s->getId()->__toString()] = sprintf('%s (%s, cargo %d/%d)', $s->getId(), $s->getType()->value, $s->getCargo()->getTotalUnits(), $s->getCargoCapacity());
        }
        $idStr = $io->choice('Transport Ship', $choices);

        return $this->shipRepository->find(new \App\Ship\ValueObject\ShipId($idStr));
    }

    private function chooseSalvageShip(SymfonyStyle $io, Player $player, bool $requireActive = false): ?\App\Ship\Model\Ship
    {
        $ships = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                if (!$ship->getType()->isSalvage() || !$ship->isReady($this->clock->now())) {
                    continue;
                }
                if ($requireActive && !$ship->isSalvaging()) {
                    continue;
                }
                $ships[] = $ship;
            }
        }
        if ($ships === []) {
            $io->note($requireActive ? 'No actively salvaging ships.' : 'No ready salvage ships.');

            return null;
        }
        $choices = [];
        foreach ($ships as $s) {
            $extra = $s->isSalvaging() ? ' [active]' : '';
            $choices[$s->getId()->__toString()] = sprintf('%s%s', $s->getId(), $extra);
        }
        $idStr = $io->choice('Salvage Ship', $choices);

        return $this->shipRepository->find(new \App\Ship\ValueObject\ShipId($idStr));
    }
}
