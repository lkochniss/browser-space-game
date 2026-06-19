<?php

declare(strict_types=1);

namespace App\Probe\Model;

use App\Common\Doctrine\Type\ProbeIdType;
use App\Planet\Model\Planet;
use App\Probe\Repository\ProbeRepository;
use App\Probe\ValueObject\ProbeId;
use App\Probe\ValueObject\ProbeType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProbeRepository::class)]
#[ORM\Table(name: 'probes')]
class Probe
{
    /**
     * Heimat-Planet (wo gebaut). Bleibt gesetzt bis die Sonde verbraucht
     * oder zerstört wird (T-013-Foundation: noch keine Deployment-Mechanik).
     */
    #[ORM\ManyToOne(targetEntity: Planet::class)]
    #[ORM\JoinColumn(name: 'planet_id', referencedColumnName: 'id', nullable: true)]
    private ?Planet $planet = null;

    /**
     * Wallclock-Build wie bei Buildings (T-062). NULL = sofort fertig.
     */
    #[ORM\Column(name: 'finished_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $finishedAt = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ProbeIdType::NAME)]
        private ProbeId $id,

        #[ORM\Column(type: 'string', length: 32, enumType: ProbeType::class)]
        private ProbeType $type,
    ) {
    }

    public function getId(): ProbeId
    {
        return $this->id;
    }

    public function getType(): ProbeType
    {
        return $this->type;
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
