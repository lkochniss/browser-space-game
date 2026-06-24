<?php

declare(strict_types=1);

namespace App\Tests\Crew\Service;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Interface\CommandBusInterface;
use App\Crew\Command\StartCrewTrainingCommand;
use App\Crew\Exception\CrewCapReachedException;
use App\Crew\Exception\MissingAcademyException;
use App\Crew\Model\Crew;
use App\Crew\Repository\CrewRepository;
use App\Crew\ValueObject\CrewStatus;
use App\Crew\ValueObject\CrewType;
use App\Planet\Model\Planet;
use App\Planet\ValueObject\PlanetId;
use App\Player\Model\Player;
use App\Player\ValueObject\PlayerId;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;

final class StartCrewTrainingCommandServiceTest extends IntegrationTestCase
{
    private CommandBusInterface $bus;
    private CrewRepository $crewRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = self::getContainer()->get(CommandBusInterface::class);
        $this->crewRepo = self::getContainer()->get(CrewRepository::class);
    }

    public function test_training_without_academy_throws(): void
    {
        $player = $this->seedPlayer(officerQuartersLevel: 1);

        $this->expectException(MissingAcademyException::class);
        $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));
    }

    public function test_training_without_officer_quarters_throws_cap_reached(): void
    {
        $player = $this->seedPlayer(academyLevel: 1, officerQuartersLevel: 0);

        $this->expectException(CrewCapReachedException::class);
        $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));
    }

    public function test_training_creates_crew_in_status_training(): void
    {
        $player = $this->seedPlayer(academyLevel: 1, officerQuartersLevel: 1);

        $crew = $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));

        self::assertInstanceOf(Crew::class, $crew);
        self::assertSame(CrewStatus::TRAINING, $crew->getStatus());
        self::assertSame(1, $crew->getLevel());
        self::assertSame(CrewType::CAPTAIN, $crew->getType());
        self::assertNotNull($crew->getTrainingFinishedAt());
        self::assertGreaterThan(new DateTimeImmutable(), $crew->getTrainingFinishedAt());
    }

    public function test_first_captain_takes_3600s_60min(): void
    {
        $player = $this->seedPlayer(academyLevel: 1, officerQuartersLevel: 1);
        $start = new DateTimeImmutable();

        $crew = $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));

        $duration = $crew->getTrainingFinishedAt()->getTimestamp() - $start->getTimestamp();
        // Etwa 3600s (Wallclock; ±5s Jitter erlaubt)
        self::assertGreaterThanOrEqual(3595, $duration);
        self::assertLessThanOrEqual(3610, $duration);
    }

    public function test_cap_blocks_second_when_cap_is_5(): void
    {
        // Officer-Quarters L1 = 5 Slots. Train 5 Captains, dann blockt 6.
        $player = $this->seedPlayer(academyLevel: 1, officerQuartersLevel: 1);
        for ($i = 0; $i < 5; ++$i) {
            $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));
        }

        $this->expectException(CrewCapReachedException::class);
        $this->bus->dispatch(new StartCrewTrainingCommand($player->getId()));
    }

    private function seedPlayer(int $academyLevel = 0, int $officerQuartersLevel = 0): Player
    {
        $player = new Player(PlayerId::generate());
        $planet = Planet::generatePlanet(PlanetId::generate());
        $player->claimPlanet($planet);

        $past = new DateTimeImmutable('-1 hour');
        if ($academyLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::ACADEMY, $academyLevel);
            $b->setFinishedAt($past);
            $planet->addBuilding($b);
        }
        if ($officerQuartersLevel > 0) {
            $b = new Building(BuildingId::generate(), BuildingType::OFFICER_QUARTERS, $officerQuartersLevel);
            $b->setFinishedAt($past);
            $planet->addBuilding($b);
        }

        $this->em->persist($player);
        $this->em->flush();

        return $player;
    }
}
