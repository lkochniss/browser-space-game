<?php

declare(strict_types=1);

namespace App\Demo\Service;

use App\Building\ValueObject\BuildingType;
use App\Demo\ValueObject\DemoGoal;
use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Repository\ShipRepository;

/**
 * T-082c Mini-Quest-System für Demo-CLI. Stateless Re-Compute aus Player-State.
 */
readonly class DemoGoalChecker
{
    public function __construct(
        private ShipRepository $shipRepository,
        private PlayerResearchRepository $playerResearchRepository,
    ) {
    }

    /**
     * @return list<DemoGoal>
     */
    public function check(Player $player): array
    {
        return [
            $this->goalHubLevel2($player),
            $this->goalAllBasicMines($player),
            $this->goalRecyclingPlant($player),
            $this->goalDebrisCollected($player),
            $this->goalSecondPlanet($player),
            $this->goalFirstResearch($player),
        ];
    }

    private function goalFirstResearch(Player $player): DemoGoal
    {
        $known = $this->playerResearchRepository->findByPlayer($player);
        $count = count($known);

        return new DemoGoal(
            label: 'Erste Forschung abschließen',
            completed: $count > 0,
            progressHint: sprintf('Forschungen abgeschlossen: %d', $count),
        );
    }

    private function goalHubLevel2(Player $player): DemoGoal
    {
        // T-172: nach Refactor ist HQ das zentrale Verwaltungs-Building (HUB ist multi-Wohnsiedlung).
        $bestLevel = 0;
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getBuildings() as $b) {
                if ($b->getType() === BuildingType::HQ) {
                    $bestLevel = max($bestLevel, $b->getLevel());
                }
            }
        }

        return new DemoGoal(
            label: 'HQ auf Level 2 ausbauen',
            completed: $bestLevel >= 2,
            progressHint: sprintf('HQ-Level: %d/2', $bestLevel),
        );
    }

    private function goalAllBasicMines(Player $player): DemoGoal
    {
        $required = [BuildingType::IRON_MINE, BuildingType::COAL_MINE, BuildingType::COPPER_MINE];
        $best = 0;
        foreach ($player->getPlanets() as $planet) {
            $found = 0;
            foreach ($required as $r) {
                foreach ($planet->getBuildings() as $b) {
                    if ($b->getType() === $r) {
                        $found++;
                        break;
                    }
                }
            }
            $best = max($best, $found);
        }

        return new DemoGoal(
            label: 'Alle 3 Basic-Mines auf einem Planeten (Iron + Coal + Copper)',
            completed: $best === 3,
            progressHint: sprintf('Mines auf bestem Planeten: %d/3', $best),
        );
    }

    private function goalRecyclingPlant(Player $player): DemoGoal
    {
        foreach ($player->getPlanets() as $planet) {
            foreach ($planet->getBuildings() as $b) {
                if ($b->getType() === BuildingType::RECYCLING_PLANT) {
                    return new DemoGoal(
                        label: 'Recycling-Plant bauen',
                        completed: true,
                        progressHint: 'gebaut auf einem Planeten',
                    );
                }
            }
        }

        return new DemoGoal(
            label: 'Recycling-Plant bauen',
            completed: false,
            progressHint: 'noch nicht gebaut',
        );
    }

    private function goalDebrisCollected(Player $player): DemoGoal
    {
        $total = 0;
        foreach ($player->getPlanets() as $planet) {
            foreach ([ResourceType::DEBRIS_LOW, ResourceType::DEBRIS_MEDIUM, ResourceType::DEBRIS_HIGH] as $type) {
                foreach ($planet->getResources() as $r) {
                    if ($r->getType() === $type) {
                        $total += $r->getAmount();
                    }
                }
            }
            foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
                foreach ([ResourceType::DEBRIS_LOW, ResourceType::DEBRIS_MEDIUM, ResourceType::DEBRIS_HIGH] as $type) {
                    $total += $ship->getCargo()->getResource($type);
                }
            }
        }

        return new DemoGoal(
            label: '50+ Debris-Items sammeln (Planet+Ship-Cargo)',
            completed: $total >= 50,
            progressHint: sprintf('Debris gesamt: %d/50', $total),
        );
    }

    private function goalSecondPlanet(Player $player): DemoGoal
    {
        $count = $player->getPlanets()->count();

        return new DemoGoal(
            label: '2. Planet kolonisieren',
            completed: $count >= 2,
            progressHint: sprintf('Planeten: %d/2', $count),
        );
    }
}
