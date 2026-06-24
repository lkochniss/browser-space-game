<?php

declare(strict_types=1);

namespace App\Tests\Crew\Service;

use App\Common\Service\AdjustableClock;
use App\Crew\Model\Crew;
use App\Crew\Service\CrewTrainingCompletionService;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class CrewTrainingCompletionServiceTest extends IntegrationTestCase
{
    public function test_training_in_future_stays_in_training(): void
    {
        $crew = $this->seedTraining(finishedIn: '+1 hour');
        $service = self::getContainer()->get(CrewTrainingCompletionService::class);

        $completed = $service->runTick();

        self::assertSame(0, $completed);
        $this->em->refresh($crew);
        self::assertSame(CrewStatus::TRAINING, $crew->getStatus());
    }

    public function test_training_in_past_completes_to_idle(): void
    {
        $crew = $this->seedTraining(finishedIn: '-1 minute');
        $service = self::getContainer()->get(CrewTrainingCompletionService::class);

        $completed = $service->runTick();

        self::assertSame(1, $completed);
        $this->em->refresh($crew);
        self::assertSame(CrewStatus::IDLE, $crew->getStatus());
        self::assertNull($crew->getTrainingFinishedAt());
        self::assertSame(1, $crew->getLevel());
    }

    public function test_advance_clock_makes_training_complete(): void
    {
        $clock = self::getContainer()->get(AdjustableClock::class);
        $crew = $this->seedTraining(finishedIn: '+5 minutes');
        $service = self::getContainer()->get(CrewTrainingCompletionService::class);

        // Vor Advance: TRAINING
        self::assertSame(0, $service->runTick());

        $clock->advanceSeconds(600); // +10 min → past
        self::assertSame(1, $service->runTick());

        $this->em->refresh($crew);
        self::assertSame(CrewStatus::IDLE, $crew->getStatus());
    }

    private function seedTraining(string $finishedIn): Crew
    {
        $player = new Player(PlayerId::generate());
        $finished = new DateTimeImmutable($finishedIn);
        $crew = Crew::startTraining($player, CrewType::CAPTAIN, $finished);

        $this->em->persist($player);
        $this->em->persist($crew);
        $this->em->flush();

        return $crew;
    }
}
