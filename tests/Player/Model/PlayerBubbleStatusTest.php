<?php

declare(strict_types=1);

namespace App\Tests\Player\Model;

use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerBubbleStatus;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;

/**
 * T-150: Bubble-Status Foundation.
 *
 * - Player default in BUBBLE
 * - exitBubble() ist idempotent
 * - Persistence-Roundtrip
 */
final class PlayerBubbleStatusTest extends IntegrationTestCase
{
    public function test_new_player_starts_in_bubble(): void
    {
        $player = new Player(PlayerId::generate());

        self::assertSame(PlayerBubbleStatus::BUBBLE, $player->getBubbleStatus());
        self::assertTrue($player->isInBubble());
    }

    public function test_exit_bubble_sets_status_exited(): void
    {
        $player = new Player(PlayerId::generate());
        $player->exitBubble();

        self::assertSame(PlayerBubbleStatus::EXITED, $player->getBubbleStatus());
        self::assertFalse($player->isInBubble());
    }

    public function test_exit_bubble_is_idempotent(): void
    {
        $player = new Player(PlayerId::generate());
        $player->exitBubble();
        $player->exitBubble();

        self::assertSame(PlayerBubbleStatus::EXITED, $player->getBubbleStatus());
    }

    public function test_bubble_status_persists_roundtrip(): void
    {
        $player = new Player(PlayerId::generate());
        $player->exitBubble();
        $this->em->persist($player);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertNotNull($reloaded);
        self::assertSame(PlayerBubbleStatus::EXITED, $reloaded->getBubbleStatus());
    }

    public function test_bubble_status_persists_default(): void
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->find(Player::class, $player->getId());
        self::assertNotNull($reloaded);
        self::assertSame(PlayerBubbleStatus::BUBBLE, $reloaded->getBubbleStatus());
    }
}
