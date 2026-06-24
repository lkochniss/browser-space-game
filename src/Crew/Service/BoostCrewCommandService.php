<?php

declare(strict_types=1);

namespace App\Crew\Service;

use App\Common\Interface\ClockInterface;
use App\Crew\Exception\BoostCooldownActiveException;
use App\Crew\Exception\CrewNotFoundException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewId;
use App\Resource\Exception\InsufficientPlayerResourcesException;
use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-104a Boost-Crew via Resource-Investment.
 *
 * Cost (Foundation):
 *   500 IRON_BAR + 100 CHIP → +500 XP
 *   Cooldown: 24h pro Crew (lastBoostAt)
 */
readonly class BoostCrewCommandService
{
    public const COOLDOWN_SECONDS = 86_400; // 24h
    public const XP_GAIN = 500;
    public const COST_IRON_BAR = 500;
    public const COST_CHIP = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private CrewRepository $crewRepository,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(CrewId $crewId): Crew
    {
        $crew = $this->crewRepository->find($crewId);
        if ($crew === null) {
            throw new CrewNotFoundException($crewId);
        }

        $now = $this->clock->now();
        $lastBoost = $crew->getLastBoostAt();
        if ($lastBoost !== null) {
            $elapsed = $now->getTimestamp() - $lastBoost->getTimestamp();
            if ($elapsed < self::COOLDOWN_SECONDS) {
                throw new BoostCooldownActiveException($crew, self::COOLDOWN_SECONDS - $elapsed);
            }
        }

        $player = $crew->getOwner();
        $costs = [
            ResourceType::IRON_BAR->value => self::COST_IRON_BAR,
            ResourceType::CHIP->value => self::COST_CHIP,
        ];
        // Aggregat-Check über alle Planeten
        $totals = $this->aggregatePlayerResources($player);
        foreach ($costs as $resVal => $needed) {
            $have = $totals[$resVal] ?? 0;
            if ($have < $needed) {
                throw new InsufficientPlayerResourcesException(
                    ResourceType::from($resVal), $needed, $have,
                );
            }
        }

        foreach ($costs as $resVal => $needed) {
            $this->deductFromPlayer($player, ResourceType::from($resVal), $needed);
        }

        $crew->addXp(self::XP_GAIN);
        $crew->recordBoost($now);
        $this->em->flush();

        return $crew;
    }

    /**
     * @return array<string, int>
     */
    private function aggregatePlayerResources(\App\Player\Model\Player $player): array
    {
        $totals = [];
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getResources() as $r) {
                $key = $r->getType()->value;
                $totals[$key] = ($totals[$key] ?? 0) + $r->getAmount();
            }
        }

        return $totals;
    }

    private function deductFromPlayer(
        \App\Player\Model\Player $player,
        ResourceType $type,
        int $amount,
    ): void {
        foreach ($player->getPlanets() as $planet) {
            if ($amount <= 0) {
                return;
            }
            foreach ($planet->getResources() as $r) {
                if ($r->getType() !== $type) {
                    continue;
                }
                $take = min($amount, $r->getAmount());
                $r->setAmount($r->getAmount() - $take);
                $amount -= $take;
                if ($amount <= 0) {
                    return;
                }
            }
        }
    }
}
