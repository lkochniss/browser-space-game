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

/**
 * Salvage extrahiert IRON_ORE (Volume-Multi 2.0 m³/Unit). Test-Helper
 * dimensioniert Cargo-Volume so, dass die Ziel-Free-Volume erreicht wird,
 * inkl. Pre-Load mit WATER (Multi 1.0 m³/Unit) zur Auffüllung.
 */
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
        // Salvage-Rate: 50 Units/min. tickAgo=2min → 100 Units extrahierbar.
        // Free-Volume=500 m³ / IRON_ORE-Multi 2.0 = 250 Units → 100 passt.
        [$ship, $field] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            freeVolumeM3: 500,
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
        // 5min × 50 = 250 möglich, aber nur 30 da. Free 1000 m³ ≫ 30 × 2 = 60 m³.
        [$ship, $field] = $this->seedActiveSalvage(
            fieldAmount: 30,
            freeVolumeM3: 1000,
            tickAgoMinutes: 5,
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
        // Capacity=Free=100 m³, IRON_ORE-Multi 2.0 → max 50 Units.
        // 5min × 50 = 250 möglich, aber Volume-Cap stoppt bei 50.
        [$ship] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            freeVolumeM3: 100,
            tickAgoMinutes: 5,
            cargoVolumeCapacity: 100,
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertSame(50, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertSame(0, $reloadedShip->maxAddableResource(ResourceType::IRON_ORE, 1));
        self::assertFalse($reloadedShip->isSalvaging(), 'cargo full → stop salvage');
    }

    public function test_skips_tick_when_no_full_unit_yet(): void
    {
        // 0 Sekunden delta → 0 Units → skip extract, ship bleibt aktiv.
        [$ship] = $this->seedActiveSalvage(
            fieldAmount: 1000,
            freeVolumeM3: 500,
            tickAgoMinutes: 0,
        );

        $this->processor->runTick();
        $this->em->flush();
        $this->em->clear();

        $reloadedShip = self::getContainer()->get(ShipRepository::class)->find($ship->getId());
        self::assertSame(0, $reloadedShip->getCargo()->getResource(ResourceType::IRON_ORE));
        self::assertTrue($reloadedShip->isSalvaging());
    }

    /**
     * @return array{Ship, AsteroidField}
     */
    private function seedActiveSalvage(
        int $fieldAmount,
        int $freeVolumeM3,
        float $tickAgoMinutes,
        int $cargoVolumeCapacity = 3000,
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
            cargoVolumeCapacity: $cargoVolumeCapacity,
        );
        $ship->setPlanet($planet);
        $ship->setFinishedAt(new DateTimeImmutable('-1 hour'));

        // Pre-Load mit WATER (1 m³/Unit) bis $freeVolumeM3 erreicht
        $usedVolume = $cargoVolumeCapacity - $freeVolumeM3;
        if ($usedVolume > 0) {
            $ship->loadResourceCargo(ResourceType::WATER, $usedVolume);
        }

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
