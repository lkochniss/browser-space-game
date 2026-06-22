<?php

declare(strict_types=1);

namespace App\Tests\POI\Command;

use App\Common\Interface\CommandBusInterface;
use App\POI\Command\BuildSpaceStationCommand;
use App\POI\Exception\StationConstructionDeprecatedException;
use App\Player\ValueObject\PlayerId;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-174: Station-Bau ist soft-deprecated (Lost-Tech-Lore). Build-Path wirft
 * immer {@see StationConstructionDeprecatedException} — egal welcher Player
 * oder welches System.
 */
final class BuildSpaceStationCommandTest extends IntegrationTestCase
{
    public function test_build_throws_deprecation_exception(): void
    {
        $bus = self::getContainer()->get(CommandBusInterface::class);

        $this->expectException(StationConstructionDeprecatedException::class);

        $bus->dispatch(new BuildSpaceStationCommand(
            PlayerId::generate(),
            SolarSystemId::generate(),
        ));
    }
}
