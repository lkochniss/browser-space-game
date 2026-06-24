<?php

declare(strict_types=1);

namespace App\Tests\Crew\Command;

use App\Common\Interface\CommandBusInterface;
use App\Crew\Command\AllocateSkillPointCommand;
use App\Crew\Exception\InsufficientSkillPointsException;
use App\Crew\Exception\TierLockViolationException;
use App\Crew\Model\Crew;
use App\Crew\ValueObject\CaptainSkillTree;
use App\Crew\ValueObject\CrewId;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;

final class AllocateSkillPointCommandTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
    }

    public function test_allocate_single_point_increments_tier(): void
    {
        $captain = $this->seedCaptain(level: 3);

        $captain = $this->bus->dispatch(new AllocateSkillPointCommand(
            $captain->getId(),
            CaptainSkillTree::BEAM_MASTER,
        ));

        self::assertSame(1, $captain->getSkillTier(CaptainSkillTree::BEAM_MASTER));
        self::assertSame(2, $captain->availableSkillPoints());
    }

    public function test_allocate_multi_points_stacks(): void
    {
        $captain = $this->seedCaptain(level: 5);

        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::SHIELD_TACTICIAN));
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::SHIELD_TACTICIAN));
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::FLEET_COMMANDER));

        $this->em->refresh($captain);
        self::assertSame(2, $captain->getSkillTier(CaptainSkillTree::SHIELD_TACTICIAN));
        self::assertSame(1, $captain->getSkillTier(CaptainSkillTree::FLEET_COMMANDER));
        self::assertSame(2, $captain->availableSkillPoints());
    }

    public function test_insufficient_points_throws(): void
    {
        $captain = $this->seedCaptain(level: 1);
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::BEAM_MASTER));

        $this->expectException(InsufficientSkillPointsException::class);
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::MISSILE_SPECIALIST));
    }

    public function test_tier_lock_blocks_after_max(): void
    {
        $captain = $this->seedCaptain(level: 10);
        for ($i = 0; $i < 5; $i++) {
            $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::BEAM_MASTER));
        }

        $this->expectException(TierLockViolationException::class);
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::BEAM_MASTER));
    }

    public function test_damage_multiplier_at_tier(): void
    {
        $captain = $this->seedCaptain(level: 10);
        for ($i = 0; $i < 3; $i++) {
            $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::BEAM_MASTER));
        }

        $this->em->refresh($captain);
        // Tier-3 → 1.20
        self::assertEqualsWithDelta(1.20, $captain->getDamageMultiplier(CaptainSkillTree::BEAM_MASTER), 0.001);
        // Wrong tree → 1.0
        self::assertEqualsWithDelta(1.0, $captain->getDamageMultiplier(CaptainSkillTree::MISSILE_SPECIALIST), 0.001);
    }

    public function test_shield_multiplier_at_tier(): void
    {
        $captain = $this->seedCaptain(level: 10);
        for ($i = 0; $i < 2; $i++) {
            $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::SHIELD_TACTICIAN));
        }

        $this->em->refresh($captain);
        // Tier-2 → 1.25
        self::assertEqualsWithDelta(1.25, $captain->getShieldMultiplier(), 0.001);
    }

    public function test_fleet_commander_tier_at_lvl(): void
    {
        $captain = $this->seedCaptain(level: 10);
        for ($i = 0; $i < 4; $i++) {
            $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::FLEET_COMMANDER));
        }

        $this->em->refresh($captain);
        self::assertSame(4, $captain->getFleetCommanderTier());
        // +0.04 × 4 = 0.16
        self::assertEqualsWithDelta(0.16, CaptainSkillTree::FLEET_COMMANDER->getFleetCommanderBoost(4), 0.001);
    }

    public function test_persistence_round_trip(): void
    {
        $captain = $this->seedCaptain(level: 5);
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::BEAM_MASTER));
        $this->bus->dispatch(new AllocateSkillPointCommand($captain->getId(), CaptainSkillTree::FLEET_COMMANDER));

        $crewId = $captain->getId();
        $this->em->clear();

        $reloaded = $this->em->find(Crew::class, $crewId);
        self::assertSame(1, $reloaded->getSkillTier(CaptainSkillTree::BEAM_MASTER));
        self::assertSame(1, $reloaded->getSkillTier(CaptainSkillTree::FLEET_COMMANDER));
        self::assertSame(0, $reloaded->getSkillTier(CaptainSkillTree::MISSILE_SPECIALIST));
        self::assertSame(0, $reloaded->getSkillTier(CaptainSkillTree::SHIELD_TACTICIAN));
    }

    private function seedCaptain(int $level): Crew
    {
        $player = new Player(PlayerId::generate());
        $captain = new Crew(
            CrewId::generate(),
            $player,
            CrewType::CAPTAIN,
            CrewStatus::IDLE,
        );
        if ($level > 1) {
            $captain->addXp(Crew::xpThresholdForLevel($level));
        }
        $this->em->persist($player);
        $this->em->persist($captain);
        $this->em->flush();

        return $captain;
    }
}
