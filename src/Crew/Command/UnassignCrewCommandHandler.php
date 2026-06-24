<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Crew\Exception\CrewNotFoundException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use Doctrine\ORM\EntityManagerInterface;

class UnassignCrewCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private CrewRepository $crewRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UnassignCrewCommand $command): Crew
    {
        $crew = $this->crewRepository->find($command->crewId);
        if ($crew === null) {
            throw new CrewNotFoundException($command->crewId);
        }
        $crew->unassign();
        $this->em->flush();

        return $crew;
    }
}
