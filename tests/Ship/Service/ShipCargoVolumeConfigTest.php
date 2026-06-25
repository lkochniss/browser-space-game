<?php

declare(strict_types=1);

namespace App\Tests\Ship\Service;

use App\Ship\Service\ShipCargoVolumeConfig;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipType;
use PHPUnit\Framework\TestCase;

final class ShipCargoVolumeConfigTest extends TestCase
{
    private ShipCargoVolumeConfig $config;

    protected function setUp(): void
    {
        $this->config = new ShipCargoVolumeConfig();
    }

    public function test_ship_type_volume_table(): void
    {
        self::assertSame(50, $this->config->getCargoVolume(ShipType::GENERIC));
        self::assertSame(300, $this->config->getCargoVolume(ShipType::COLONY_SHIP));
        self::assertSame(100, $this->config->getCargoVolume(ShipType::TRANSPORT_SMALL));
        self::assertSame(500, $this->config->getCargoVolume(ShipType::TRANSPORT_MEDIUM));
        self::assertSame(2000, $this->config->getCargoVolume(ShipType::TRANSPORT_LARGE));
        self::assertSame(500, $this->config->getCargoVolume(ShipType::SALVAGE));
    }

    public function test_combat_classes_base_mk1(): void
    {
        self::assertSame(50, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::FRIGATE_MK1));
        self::assertSame(80, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::DESTROYER_MK1));
        self::assertSame(120, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::CRUISER_MK1));
        self::assertSame(200, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::BATTLESHIP_MK1));
        self::assertSame(150, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::CARRIER_MK1));
    }

    public function test_mk_multipliers(): void
    {
        // Frigate Mk I = 50, Mk II = 75 (× 1.5), Mk III = 113 (× 2.25, rounded)
        self::assertSame(50, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::FRIGATE_MK1));
        self::assertSame(75, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::FRIGATE_MK2));
        self::assertSame(113, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::FRIGATE_MK3));

        // Battleship Mk I = 200, Mk II = 300, Mk III = 450
        self::assertSame(200, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::BATTLESHIP_MK1));
        self::assertSame(300, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::BATTLESHIP_MK2));
        self::assertSame(450, $this->config->getCargoVolume(ShipType::GENERIC, ShipClass::BATTLESHIP_MK3));
    }
}
