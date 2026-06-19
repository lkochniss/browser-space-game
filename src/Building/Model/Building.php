<?php

declare(strict_types=1);

namespace App\Building\Model;

use App\Building\Repository\BuildingRepository;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use App\Common\Doctrine\Type\BuildingIdType;
use App\Planet\Model\Planet;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
#[ORM\Table(name: 'buildings')]
class Building
{
    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'buildings')]
    #[ORM\JoinColumn(name: 'planet_id', referencedColumnName: 'id', nullable: true)]
    private ?Planet $planet = null;

    /**
     * Real-time construction completion timestamp. NULL = ready instantly.
     * T-062 will use this for the wall-clock construction mechanic; T-009 leaves it null.
     */
    #[ORM\Column(name: 'finished_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $finishedAt = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: BuildingIdType::NAME)]
        private BuildingId $id,

        #[ORM\Column(type: 'string', length: 32, enumType: BuildingType::class)]
        private BuildingType $type,

        #[ORM\Column(type: 'integer')]
        private int $level,
    ) {
    }

    public static function createNewBuilding(BuildingType $type): self
    {
        return new self(
            BuildingId::generate(),
            $type,
            1
        );
    }

    public function getId(): BuildingId
    {
        return $this->id;
    }

    public function getType(): BuildingType
    {
        return $this->type;
    }

    public function setType(BuildingType $type): void
    {
        $this->type = $type;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getPlanet(): ?Planet
    {
        return $this->planet;
    }

    public function setPlanet(?Planet $planet): void
    {
        $this->planet = $planet;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?DateTimeImmutable $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * T-062: Building wirkt nur, wenn Bauzeit abgelaufen ist.
     *
     * Semantik:
     * - `finishedAt === null` → instant ready (Legacy / Test-Fixtures ohne Wall-Clock)
     * - `finishedAt !== null && now === null` → konservativ: NICHT ready
     *   (kein Clock-Kontext zur Verfügung — Aufrufer muss Clock liefern)
     * - `finishedAt !== null && now !== null` → ready wenn `finishedAt <= now`
     */
    public function isReady(?DateTimeImmutable $now = null): bool
    {
        if ($this->finishedAt === null) {
            return true;
        }
        if ($now === null) {
            return false;
        }

        return $this->finishedAt <= $now;
    }
}
