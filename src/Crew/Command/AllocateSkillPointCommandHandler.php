<?php

declare(strict_types=1);

namespace App\Crew\Command;

use App\Common\Interface\CommandHandlerInterface;
use App\Crew\Exception\CrewNotFoundException;
use App\Crew\Exception\InsufficientSkillPointsException;
use App\Crew\Exception\TierLockViolationException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CaptainSkillTree;
use Doctrine\ORM\EntityManagerInterface;

class AllocateSkillPointCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private CrewRepository $repo,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(AllocateSkillPointCommand $command): Crew
    {
        $crew = $this->repo->find($command->crewId);
        if ($crew === null) {
            throw new CrewNotFoundException($command->crewId);
        }

        if ($crew->availableSkillPoints() <= 0) {
            throw new InsufficientSkillPointsException($command->crewId);
        }

        $allocation = $crew->getSkillAllocation();
        $currentTier = $allocation->getTier($command->tree);
        if ($currentTier >= CaptainSkillTree::MAX_TIER) {
            throw new TierLockViolationException($command->tree, $currentTier);
        }

        $crew->applySkillAllocation($allocation->withIncrement($command->tree));
        $this->em->flush();

        return $crew;
    }
}
