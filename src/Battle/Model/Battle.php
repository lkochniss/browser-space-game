<?php

declare(strict_types=1);

namespace App\Battle\Model;

use App\Battle\Repository\BattleRepository;
use App\Battle\ValueObject\BattleId;
use App\Battle\ValueObject\BattleStatus;
use App\Common\Doctrine\Type\BattleIdType;
use App\Fleet\Model\Fleet;
use App\Planet\Model\Planet;
use App\Player\Model\Player;
use App\SolarSystem\Model\SolarSystem;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-103 Battle-Entity. Persistiert nur das End-Resultat + Round-Count.
 * Round-by-Round Replay-Log = T-103d (Out-of-Scope hier).
 *
 * Defender ist polymorph: entweder gegen eine `defenderFleet` (Fleet-vs-
 * Fleet im selben System) ODER gegen einen `defenderPlanet` (Planet-Defense
 * mit Shield/Turret/AA-Stack aus T-068). Genau eines ist non-null.
 */
#[ORM\Entity(repositoryClass: BattleRepository::class)]
#[ORM\Table(name: 'battles')]
class Battle
{
    #[ORM\Column(name: 'status', type: 'string', length: 32, enumType: BattleStatus::class)]
    private BattleStatus $status = BattleStatus::RUNNING;

    #[ORM\Column(name: 'rounds', type: 'integer', options: ['default' => 0])]
    private int $rounds = 0;

    #[ORM\Column(name: 'started_at', type: 'datetime_immutable')]
    private DateTimeImmutable $startedAt;

    #[ORM\Column(name: 'ended_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endedAt = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: BattleIdType::NAME)]
        private BattleId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'attacker_player_id', referencedColumnName: 'id', nullable: true)]
        private ?Player $attacker,

        #[ORM\ManyToOne(targetEntity: Fleet::class)]
        #[ORM\JoinColumn(name: 'attacker_fleet_id', referencedColumnName: 'id', nullable: true)]
        private ?Fleet $attackerFleet,

        #[ORM\ManyToOne(targetEntity: Fleet::class)]
        #[ORM\JoinColumn(name: 'defender_fleet_id', referencedColumnName: 'id', nullable: true)]
        private ?Fleet $defenderFleet = null,

        #[ORM\ManyToOne(targetEntity: Planet::class)]
        #[ORM\JoinColumn(name: 'defender_planet_id', referencedColumnName: 'id', nullable: true)]
        private ?Planet $defenderPlanet = null,

        #[ORM\ManyToOne(targetEntity: SolarSystem::class)]
        #[ORM\JoinColumn(name: 'location_system_id', referencedColumnName: 'id', nullable: true)]
        private ?SolarSystem $location = null,
    ) {
        $this->startedAt = new DateTimeImmutable();
    }

    public function getId(): BattleId
    {
        return $this->id;
    }

    public function getAttacker(): ?Player
    {
        return $this->attacker;
    }

    public function getAttackerFleet(): ?Fleet
    {
        return $this->attackerFleet;
    }

    public function getDefenderFleet(): ?Fleet
    {
        return $this->defenderFleet;
    }

    public function getDefenderPlanet(): ?Planet
    {
        return $this->defenderPlanet;
    }

    public function getLocation(): ?SolarSystem
    {
        return $this->location;
    }

    public function getStatus(): BattleStatus
    {
        return $this->status;
    }

    public function getRounds(): int
    {
        return $this->rounds;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function incrementRound(): void
    {
        ++$this->rounds;
    }

    public function endWith(BattleStatus $status, ?DateTimeImmutable $now = null): void
    {
        $this->status = $status;
        $this->endedAt = $now ?? new DateTimeImmutable();
    }
}
