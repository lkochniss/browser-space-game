<?php

declare(strict_types=1);

namespace App\Tests\Ship\Service;

use App\POI\Model\AsteroidField;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\Service\SalvageProcessor;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class SalvageProcessorTest extends IntegrationTestCase
{
    private SalvageProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = self::getContainer()->get(SalvageProcessor::class);
    }

    public function test_extracts_resources_per_minute_rate(): void
    {
        // Salvage-Rate: 50 Units/min. Wir setzen lastTickAt auf -2min,
        // also sollte 100 Units extrahiert werden.
        [$ship, $field] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            cargoFree: 500,
            tickAgoMinutes: 2,
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        $reloadedField = self::getContainer()->get(PoiRepository::class)->find($field->getId());

        self::assertSame(100, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertSame(900, $reloadedField->getAmount(ResourceType::IRON_ORE));
        self::assertTrue($reloadedShip->isSalvaging(), 'ship still salvaging after partial extract');
    }

    public function test_stops_when_field_empty_and_removes_field(): void
    {
        [$ship, $field] = $this->seedActiveSalvage(
            fieldAmount: 30,
            cargoFree: 1000,
            tickAgoMinutes: 5, // 5min × 50 = 250, aber nur 30 da
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        $reloadedField = self::getContainer()->get(PoiRepository::class)->find($field->getId());

        self::assertSame(30, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertNull($reloadedField, 'empty field is removed by processor');
        self::assertFalse($reloadedShip->isSalvaging(), 'ship stopped salvaging');
    }

    public function test_stops_when_cargo_full(): void
    {
        // cargoCapacity 100 → cargoFree 100 → mit 50/min × 5min wäre 250 möglich,
        // aber nur 100 gehen ins Cargo.
        [$ship, $field] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            cargoFree: 100,
            tickAgoMinutes: 5,
            cargoCapacity: 100,
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertSame(100, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertSame(0, $reloadedShip->getCargoFreeUnits());
        self::assertFalse($reloadedShip->isSalvaging(), 'cargo full → stop salvage');
    }

    public function test_skips_tick_when_no_full_unit_yet(): void
    {
        // 0 Sekunden delta → 0 Units → skip extract, ship bleibt aktiv.
        [$ship] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            cargoFree: 500,
            tickAgoMinutes: 0,
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        // Iron-Ore-Cargo bleibt 0 (pre-loaded COAL bleibt im Cargo unangetastet)
        self::assertSame(0, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertTrue($reloadedShip->isSalvaging());
    }

    /**
     * @return array{Ship, AsteroidField}
     */
    private function seedActiveSalvage(
        int $fieldAmount,
        int $cargoFree,
        float $tickAgoMinutes,
        int $cargoCapacity = 3000,
    ): array {
        $player = new Player(PlayerId::generate());
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $planet = Planet::generatePlanet(PlanetId::generate());
        $planet->setSolarSystem($system);
        $player->claimPlanet($planet);

        $field = new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $system,
            contents: [ResourceType::IRON_ORE->value => $fieldAmount],
        );

        $ship = new Ship(
            id: ShipId::generate(),
            type: ShipType::SALVAGE,
            populationAssigned: 25,
            cargoCapacity: $cargoCapacity,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        // Pre-Load Cargo um cargoFree zu erreichen
        $usedCargo = $cargoCapacity - $cargoFree;
        if ($usedCargo > 0) {
            $ship->loadResourceCargo(ResourceType::COAL, $usedCargo);
        }

        // Salvage State anlegen mit lastTickAt = now - tickAgoMinutes
        $tickAt = (new DateTimeImmutable())->modify(sprintf('-%d seconds', (int) ($tickAgoMinutes * 60)));
        $ship->startSalvage($field->getId()->__toString(), ResourceType::IRON_ORE, $tickAt);

        $this->em->persist($player);
        $this->em->persist($system);
        $this->em->persist($field);
        $this->em->persist($ship);
        $this->em->flush();

        return [$ship, $field];
    }
}
