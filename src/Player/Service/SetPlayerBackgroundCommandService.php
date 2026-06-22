<?php

declare(strict_types=1);

namespace App\Player\Service;

use App\Player\Exception\PlayerNotFoundException;
use App\Player\Model\Player;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerBackground;
use App\Player\ValueObject\PlayerId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-122: Setzt den Player-Background permanent.
 * `Player::setBackground()` wirft `BackgroundAlreadySetException` falls bereits
 * gesetzt — Service propagiert das.
 */
readonly class SetPlayerBackgroundCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerRepository $playerRepository,
    ) {
    }

    public function __invoke(PlayerId $playerId, PlayerBackground $background): Player
    {
        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            throw new PlayerNotFoundException($playerId);
        }

        $player->setBackground($background);
        $this->em->flush();

        return $player;
    }
}
