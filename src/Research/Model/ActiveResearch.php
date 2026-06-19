<?php

declare(strict_types=1);

namespace App\Research\Model;

use App\Common\Doctrine\Type\ResearchIdType;
use App\Player\Model\Player;
use App\Research\Repository\ActiveResearchRepository;
use App\Research\ValueObject\ResearchId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-025: aktive Forschung. UNIQUE auf player_id (kein Index, sondern hard
 * Unique → 1 aktive Forschung pro Player-Decision).
 *
 * Bei Tick-Resolve: wenn `finished_at <= now`, wird PlayerResearch.level++ und
 * die ActiveResearch entfernt. Decision: bei Reset ohne Forschung-Cancel-
 * Erstattung (Kosten weg) — Cancel-Mechanik kommt später bei Bedarf.
 */
#[ORM\Entity(repositoryClass: ActiveResearchRepository::class)]
#[ORM\Table(name: 'active_research')]
#[ORM\UniqueConstraint(name: 'uniq_player', columns: ['player_id'])]
class ActiveResearch
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ResearchIdType::NAME)]
        private ResearchId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: false)]
        private Player $player,

        #[ORM\Column(name: 'node_slug', type: 'string', length: 64)]
        private string $nodeSlug,

        #[ORM\Column(name: 'target_level', type: 'integer')]
        private int $targetLevel,

        #[ORM\Column(name: 'started_at', type: 'datetime_immutable')]
        private DateTimeImmutable $startedAt,

        #[ORM\Column(name: 'finished_at', type: 'datetime_immutable')]
        private DateTimeImmutable $finishedAt,
    ) {
    }

    public static function generate(
        Player $player,
        string $nodeSlug,
        int $targetLevel,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $finishedAt,
    ): self {
        return new self(ResearchId::generate(), $player, $nodeSlug, $targetLevel, $startedAt, $finishedAt);
    }

    public function getId(): ResearchId
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getNodeSlug(): string
    {
        return $this->nodeSlug;
    }

    public function getTargetLevel(): int
    {
        return $this->targetLevel;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function isFinished(DateTimeImmutable $now): bool
    {
        return $this->finishedAt <= $now;
    }
}
