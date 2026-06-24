<?php

declare(strict_types=1);

namespace App\Crew\Service;

use App\Common\Interface\ClockInterface;
use App\Crew\Repository\CrewRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-104a Globaler Tick-Service (analog FleetArrivalService): pro Tick checkt
 * alle Crew in TRAINING-Status, ob `trainingFinishedAt <= now` → IDLE.
 */
readonly class CrewTrainingCompletionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CrewRepository $crewRepository,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Returns die Anzahl der completed Trainings.
     */
    public function runTick(): int
    {
        $now = $this->clock->now();
        $completed = 0;
        foreach ($this->crewRepository->findInTraining() as $crew) {
            if ($crew->isTrainingDone($now)) {
                $crew->completeTraining();
                ++$completed;
            }
        }
        if ($completed > 0) {
            $this->em->flush();
        }

        return $completed;
    }
}
