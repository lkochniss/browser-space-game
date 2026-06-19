<?php

declare(strict_types=1);

namespace App\Tests\Faction\Service;

use App\Faction\Exception\HostileFactionRepLockedException;
use App\Faction\Repository\FactionRepository;
use App\Faction\Repository\PlayerFactionReputationRepository;
use App\Faction\Service\ReputationService;
use App\Faction\ValueObject\ReputationTier;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;

final class ReputationServiceTest extends IntegrationTestCase
{
    private ReputationService $service;
    private FactionRepository $factionRepo;
    private PlayerFactionReputationRepository $repRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = self::getContainer()->get(ReputationService::class);
        $this->factionRepo = self::getContainer()->get(FactionRepository::class);
        $this->repRepo = self::getContainer()->get(PlayerFactionReputationRepository::class);
    }

    public function test_get_reputation_returns_default_without_row(): void
    {
        $player = $this->persistPlayer();
        $merchant = $this->factionRepo->findBySlug('merchant_guild');

        self::assertSame(0, $this->service->getReputation($player, $merchant));
        self::assertNull($this->repRepo->findByPlayerAndFaction($player, $merchant));
    }

    public function test_get_reputation_returns_default_for_hostile_without_row(): void
    {
        $player = $this->persistPlayer();
        $pirate = $this->factionRepo->findBySlug('pirate_consortium');

        self::assertSame(-100, $this->service->getReputation($player, $pirate));
        self::assertSame(ReputationTier::HOSTILE, $this->service->getTier($player, $pirate));
    }

    public function test_change_reputation_creates_row_lazy_and_persists(): void
    {
        $player = $this->persistPlayer();
        $merchant = $this->factionRepo->findBySlug('merchant_guild');
        $playerId = $player->getId();

        $newValue = $this->service->changeReputation($player, $merchant, 25);
        $this->em->flush();

        self::assertSame(25, $newValue);
        $this->em->clear();

        $reloadedPlayer = $this->em->find(Player::class, $playerId);
        $reloadedMerchant = $this->factionRepo->findBySlug('merchant_guild');
        $reloadedRow = $this->repRepo->findByPlayerAndFaction($reloadedPlayer, $reloadedMerchant);

        self::assertNotNull($reloadedRow);
        self::assertSame(25, $reloadedRow->getValue());
    }

    public function test_change_reputation_clamps_at_upper_bound(): void
    {
        $player = $this->persistPlayer();
        $merchant = $this->factionRepo->findBySlug('merchant_guild');

        $this->service->changeReputation($player, $merchant, 80);
        $this->em->flush();
        $value = $this->service->changeReputation($player, $merchant, 50);
        $this->em->flush();

        self::assertSame(100, $value);
    }

    public function test_change_reputation_clamps_at_lower_bound(): void
    {
        $player = $this->persistPlayer();
        $merchant = $this->factionRepo->findBySlug('merchant_guild');

        $value = $this->service->changeReputation($player, $merchant, -200);
        $this->em->flush();

        self::assertSame(-100, $value);
    }

    public function test_change_reputation_throws_for_always_hostile_faction(): void
    {
        $player = $this->persistPlayer();
        $pirate = $this->factionRepo->findBySlug('pirate_consortium');

        $this->expectException(HostileFactionRepLockedException::class);
        $this->service->changeReputation($player, $pirate, 50);
    }

    private function persistPlayer(): Player
    {
        $player = new Player(PlayerId::generate());
        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }
}
