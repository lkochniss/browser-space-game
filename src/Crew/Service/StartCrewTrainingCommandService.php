<?php

declare(strict_types=1);

namespace App\Crew\Service;

use App\Common\Interface\ClockInterface;
use App\Crew\Exception\CrewCapReachedException;
use App\Crew\Exception\MissingAcademyException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewType;
use App\Player\Exception\PlayerNotFoundException;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerId;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-104a Crew-Training Foundation:
 *  - Player muss eine fertige ACADEMY haben
 *  - Cap-Check via Officer-Quarters (Player::getCrewCap)
 *  - Wallclock-Duration = CrewType-spezifische Formel (Captain: 60min × 2^count)
 */
readonly class StartCrewTrainingCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepository,
        private CrewRepository $crewRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlayerId $playerId, CrewType $type = CrewType::CAPTAIN): Crew
    {
        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new PlayerNotFoundException($playerId);
        }

        $now = $this->clock->now();

        if (!$player->hasAnyAcademy($now)) {
            throw new MissingAcademyException();
        }

        $aliveTotal = $this->crewRepository->countAliveByPlayer($player);
        $cap = $player->getCrewCap($now);
        if ($aliveTotal >= $cap) {
            throw new CrewCapReachedException($aliveTotal, $cap);
        }

        $aliveOfType = $this->crewRepository->countAliveByPlayerAndType($player, $type);
        $duration = $type->getTrainingDurationSeconds($aliveOfType);
        $finishedAt = $now->add(new DateInterval(sprintf('PT%dS', $duration)));

        $crew = Crew::startTraining($player, $type, $finishedAt);
        $this->em->persist($crew);
        $this->em->flush();

        return $crew;
    }
}
