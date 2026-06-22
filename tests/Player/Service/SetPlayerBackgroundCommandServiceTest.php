<?php

declare(strict_types=1);

namespace App\Tests\Player\Service;

use App\Common\Interface\CommandBusInterface;
use App\Player\Command\SetPlayerBackgroundCommand;
use App\Player\Exception\BackgroundAlreadySetException;
use App\Player\Exception\PlayerNotFoundException;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerBackground;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-122 Player-Background Foundation:
 *  - Default NULL
 *  - setBackground() persistiert
 *  - Re-Spec wirft BackgroundAlreadySetException
 *  - Player not found → Exception
 */
final class SetPlayerBackgroundCommandServiceTest extends IntegrationTestCase
{
    public function test_new_player_has_no_background(): void
    {
        $player = new Player(PlayerId::generate());

        self::assertNull($player->getBackground());
    }

    public function test_set_background_persists(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        $bus = self::getContainer()->get(CommandBusInterface::class);
        $bus->dispatch(new SetPlayerBackgroundCommand(
            $player->getId(),
            PlayerBackground::TECH_ADEPT,
        ));

        $this->em->clear();
        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertNotNull($reloaded);
        self::assertSame(PlayerBackground::TECH_ADEPT, $reloaded->getBackground());
    }

    public function test_re_spec_throws(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        $bus = self::getContainer()->get(CommandBusInterface::class);
        $bus->dispatch(new SetPlayerBackgroundCommand(
            $player->getId(),
            PlayerBackground::IMPERIAL_NOBILITY,
        ));

        $this->expectException(BackgroundAlreadySetException::class);
        $bus->dispatch(new SetPlayerBackgroundCommand(
            $player->getId(),
            PlayerBackground::VETERAN_PILOT,
        ));
    }

    public function test_player_not_found_throws(): void
    {
        $bus = self::getContainer()->get(CommandBusInterface::class);

        $this->expectException(PlayerNotFoundException::class);
        $bus->dispatch(new SetPlayerBackgroundCommand(
            PlayerId::generate(),
            PlayerBackground::COMMON_BORN,
        ));
    }
}
