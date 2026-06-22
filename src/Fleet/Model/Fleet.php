<?php

declare(strict_types=1);

namespace App\Fleet\Model;

use App\Common\Doctrine\Type\FleetIdType;
use App\Fleet\Repository\FleetRepository;
use App\Fleet\ValueObject\FleetId;
use App\Fleet\ValueObject\FleetStatus;
use App\Planet\Model\Planet;
use App\Player\Model\Player;
use App\Ship\Model\Ship;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-017 Fleet-Container. Persistent (User-Decision):
 * - Player legt Fleet manuell an via CreateFleetCommand
 * - Schiffe sind in genau einer Fleet (Doc-Vorgabe)
 * - Fleet hat Status DOCKED (am originPlanet) oder IN_TRANSIT (ships.planet=null)
 * - Bei Ankunft (FleetArrivalService): status=DOCKED, originPlanet=targetPlanet,
 *   targetPlanet=null
 *
 * Travel-Time = baseDuration / min(ship.type.getSpeed) — langsamstes Schiff
 * bestimmt die Fleet-Speed.
 */
#[ORM\Entity(repositoryClass: FleetRepository::class)]
#[ORM\Table(name: 'fleets')]
class Fleet
{
    /** @var Collection<int, Ship> */
    #[ORM\OneToMany(targetEntity: Ship::class, mappedBy: 'fleet')]
    private Collection $ships;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: FleetIdType::NAME)]
        private FleetId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: false)]
        private Player $player,

        #[ORM\Column(type: 'string', length: 32, enumType: FleetStatus::class)]
        private FleetStatus $status,

        #[ORM\ManyToOne(targetEntity: Planet::class)]
        #[ORM\JoinColumn(name: 'origin_planet_id', referencedColumnName: 'id', nullable: true)]
        private ?Planet $originPlanet = null,

        #[ORM\ManyToOne(targetEntity: Planet::class)]
        #[ORM\JoinColumn(name: 'target_planet_id', referencedColumnName: 'id', nullable: true)]
        private ?Planet $targetPlanet = null,

        #[ORM\Column(name: 'departed_at', type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $departedAt = null,

        #[ORM\Column(name: 'arrived_at', type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $arrivedAt = null,
    ) {
        $this->ships = new ArrayCollection();
    }

    public function getId(): FleetId
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getStatus(): FleetStatus
    {
        return $this->status;
    }

    public function setStatus(FleetStatus $status): void
    {
        $this->status = $status;
    }

    public function getOriginPlanet(): ?Planet
    {
        return $this->originPlanet;
    }

    public function setOriginPlanet(?Planet $planet): void
    {
        $this->originPlanet = $planet;
    }

    public function getTargetPlanet(): ?Planet
    {
        return $this->targetPlanet;
    }

    public function setTargetPlanet(?Planet $planet): void
    {
        $this->targetPlanet = $planet;
    }

    public function getDepartedAt(): ?DateTimeImmutable
    {
        return $this->departedAt;
    }

    public function setDepartedAt(?DateTimeImmutable $at): void
    {
        $this->departedAt = $at;
    }

    public function getArrivedAt(): ?DateTimeImmutable
    {
        return $this->arrivedAt;
    }

    public function setArrivedAt(?DateTimeImmutable $at): void
    {
        $this->arrivedAt = $at;
    }

    /**
     * @return Collection<int, Ship>
     */
    public function getShips(): Collection
    {
        return $this->ships;
    }

    /**
     * Bidirektional: setzt ship.fleet + fügt zur ships-Collection hinzu.
     * Erforderlich, weil Doctrine OneToMany sonst lazy + einseitig befüllt wird —
     * Fleet sieht die frisch zugewiesenen Ships sonst erst nach em->refresh.
     */
    public function attachShip(Ship $ship): void
    {
        if (!$this->ships->contains($ship)) {
            $this->ships->add($ship);
        }
        if ($ship->getFleet() !== $this) {
            $ship->setFleet($this);
        }
    }

    public function detachShip(Ship $ship): void
    {
        if ($this->ships->contains($ship)) {
            $this->ships->removeElement($ship);
        }
        if ($ship->getFleet() === $this) {
            $ship->setFleet(null);
        }
    }

    public function isDocked(): bool
    {
        return $this->status === FleetStatus::DOCKED;
    }

    public function isInTransit(): bool
    {
        return $this->status === FleetStatus::IN_TRANSIT;
    }

    /**
     * Langsamstes Schiff bestimmt die Fleet-Speed (T-017 User-Decision).
     * T-026c: nutzt jetzt `Ship::getEffectiveSpeed()` (= ShipType × Propulsion-
     * Multiplier), nicht mehr nur ShipType.getSpeed.
     *
     * Liefert 1.0 als Default für leere Fleets (sollte nie aufgerufen werden).
     */
    public function getMinSpeed(): float
    {
        $minSpeed = null;
        foreach ($this->ships as $ship) {
            $speed = $ship->getEffectiveSpeed();
            if ($minSpeed === null || $speed < $minSpeed) {
                $minSpeed = $speed;
            }
        }

        return $minSpeed ?? 1.0;
    }
}
