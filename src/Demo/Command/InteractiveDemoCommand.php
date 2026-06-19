<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Building\Command\BuildBuildingCommand;
use App\Building\Command\UpgradeBuildingCommand;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Common\Service\AdjustableClock;
use App\Faction\Service\FactionSeedService;
use App\Fleet\Command\CreateFleetCommand;
use App\Fleet\Command\DisbandFleetCommand;
use App\Fleet\Command\MoveFleetCommand;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\Service\FleetArrivalService;
use App\Fleet\ValueObject\FleetStatus;
use App\GameState\Model\GameState;
use App\POI\Model\AsteroidField;
use App\POI\Model\Nebula;
use App\POI\Model\SpaceStation;
use App\POI\Model\Wormhole;
use App\POI\Repository\PoiRepository;
use App\Planet\Command\ClaimStartPlanetCommand;
use App\Planet\Command\ColonizePlanetCommand;
use App\Planet\Repository\PlanetRepository;
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
use App\Ship\ValueObject\ShipType;
use App\Tick\Engine\TickEngine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdjustableClock $clock,
        private readonly PlayerRepository $playerRepository,
        private readonly PlanetRepository $planetRepository,
        private readonly ShipRepository $shipRepository,
        private readonly FleetRepository $fleetRepository,
        private readonly PoiRepository $poiRepository,
        private readonly FactionSeedService $factionSeed,
        private readonly CommandBusInterface $bus,
        private readonly TickEngine $tickEngine,
        private readonly FleetArrivalService $fleetArrival,
        private readonly SalvageProcessor $salvageProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Drop schema + new player');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Browser Space Game — Interactive Demo Sandbox');

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

            try {
                $continue = match ($action) {
                    'Status' => $this->showStatus($io, $player),
                    'Build Building' => $this->buildBuilding($io, $player),
                    'Upgrade Building' => $this->upgradeBuilding($io, $player),
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
                    'Forschung (T-025 Stub)' => $this->researchStub($io),
                    'Reset Demo' => $this->resetSession($io, $player),
                    'Quit' => false,
                    default => true,
                };
            } catch (Throwable $e) {
                $io->error(sprintf('Error: %s', $e->getMessage()));
                $continue = true;
            }

            // Reload Player after potential mutation/clear
            $player = $this->playerRepository->find($player->getId());
            if ($player === null) {
                $io->warning('Player no longer exists — exiting.');

                return Command::SUCCESS;
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
            'Build Building',
            'Upgrade Building',
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
            'Forschung (T-025 Stub)',
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

            return $player;
        }

        // Resume existing
        $io->note(sprintf('Resuming with existing player %s. Use --reset for fresh state.', $players[0]->getId()));

        return $players[0];
    }

    private function showStatus(SymfonyStyle $io, Player $player): bool
    {
        $io->section(sprintf('Status — Player %s — Clock: %s', $player->getId(), $this->clock->now()->format('Y-m-d H:i:s')));

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

    private function buildBuilding(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }

        $type = $io->choice('Building Type', array_map(fn ($c) => $c->value, BuildingType::cases()));
        $this->bus->dispatch(new BuildBuildingCommand($planet->getId(), BuildingType::from($type)));
        $io->success(sprintf('Build started: %s on %s', $type, $planet->getId()));

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
        $type = $io->choice('Ship Type', array_map(fn ($c) => $c->value, ShipType::cases()));
        $ship = $this->bus->dispatch(new BuildShipCommand($planet->getId(), ShipType::from($type)));
        $io->success(sprintf('Building Ship %s (%s) — finishedAt %s', $ship->getId(), $type, $ship->getFinishedAt()?->format('H:i:s') ?? '—'));

        return true;
    }

    private function buildProbe(SymfonyStyle $io, Player $player): bool
    {
        $planet = $this->choosePlayerPlanet($io, $player);
        if ($planet === null) {
            return true;
        }
        $type = $io->choice('Probe Type', array_map(fn ($c) => $c->value, ProbeType::cases()));
        $probe = $this->bus->dispatch(new BuildProbeCommand($planet->getId(), ProbeType::from($type)));
        $io->success(sprintf('Building Probe %s (%s)', $probe->getId(), $type));

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
        $asteroids = array_filter($pois, fn ($p) => $p instanceof AsteroidField);
        if ($asteroids === []) {
            $io->note('No asteroid fields in this system.');

            return true;
        }

        $choices = [];
        foreach ($asteroids as $a) {
            $choices[$a->getId()->__toString()] = sprintf('%s (contents %d)', $a->getId(), $a->getTotalAmount());
        }
        $poiIdStr = $io->choice('Asteroid Field', $choices);

        /** @var AsteroidField $field */
        $field = $this->poiRepository->find(new \App\POI\ValueObject\PoiId($poiIdStr));
        $resourceChoices = [];
        foreach ($field->getContents() as $resVal => $amount) {
            $resourceChoices[$resVal] = sprintf('%s (%d)', $resVal, $amount);
        }
        $resVal = $io->choice('Resource to extract', $resourceChoices);

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

        $io->success(sprintf(
            'Tick advanced by %ds — Clock: %s | Fleets arrived: %d | Salvages processed: %d',
            $seconds,
            $this->clock->now()->format('Y-m-d H:i:s'),
            $arrived,
            $salvaged,
        ));

        return true;
    }

    private function researchStub(SymfonyStyle $io): bool
    {
        $io->note('Forschungs-Framework noch nicht implementiert. Siehe T-025 (Open).');

        return true;
    }

    private function resetSession(SymfonyStyle $io, Player $player): bool
    {
        if (!$io->confirm('Wirklich alles löschen und frischen Player anlegen?', false)) {
            return true;
        }
        $tool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
        $this->factionSeed->seed();

        $playerId = PlayerId::generate();
        $planetId = \App\Planet\ValueObject\PlanetId::generate();
        $this->bus->dispatch(new ClaimStartPlanetCommand($playerId, $planetId));

        $io->success('Demo state reset.');

        return true;
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
