<?php

declare(strict_types=1);

namespace App\Player\Model;

use App\Common\Doctrine\Type\PlayerIdType;
use App\Planet\Model\Planet;
use App\Player\Exception\BackgroundAlreadySetException;
use App\Player\Repository\PlayerRepository;
use App\Player\ValueObject\PlayerBackground;
use App\Player\ValueObject\PlayerBubbleStatus;
use App\Player\ValueObject\PlayerId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'players')]
class Player
{
    /** @var Collection<int, Planet> */
    #[ORM\OneToMany(
        targetEntity: Planet::class,
        mappedBy: 'player',
        cascade: ['persist'],
    )]
    private Collection $planets;

    /**
     * T-150: Anti-Crush-Tutorial-Phase. Default `BUBBLE` bei Player-Create;
     * `ColonizePlanetCommandService` setzt nach 2. erfolgreichem Claim auf
     * `EXITED`.
     */
    #[ORM\Column(name: 'bubble_status', type: 'string', length: 16, enumType: PlayerBubbleStatus::class)]
    private PlayerBubbleStatus $bubbleStatus = PlayerBubbleStatus::BUBBLE;

    /**
     * T-122: Player-Background (40k-Imperial-Flavor). Permanent gesetzt nach
     * Onboarding (T-046) bzw. via Demo-CLI-Action. NULL = noch nicht gewählt.
     * Effect-Resolver-Hooks (Multiplier-Anwendung) folgen in T-122b.
     */
    #[ORM\Column(name: 'background', type: 'string', length: 32, enumType: PlayerBackground::class, nullable: true)]
    private ?PlayerBackground $background = null;

    /**
     * T-096 Player-History-Stats Foundation. Lifetime-Counters; hochgezählt
     * über Hooks in den jeweiligen Command-Services nach Erfolg.
     * Mining-Total + Battle-Counters + factionRepLifetime + XP-Integration
     * folgen in T-096b.
     */
    #[ORM\Column(name: 'stats_buildings_built', type: 'integer', options: ['default' => 0])]
    private int $statsBuildingsBuilt = 0;

    #[ORM\Column(name: 'stats_planets_colonized', type: 'integer', options: ['default' => 0])]
    private int $statsPlanetsColonized = 0;

    #[ORM\Column(name: 'stats_ships_built', type: 'integer', options: ['default' => 0])]
    private int $statsShipsBuilt = 0;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: PlayerIdType::NAME)]
        private PlayerId $id,
    ) {
        $this->planets = new ArrayCollection();
    }

    public function getId(): PlayerId
    {
        return $this->id;
    }

    /** @return Collection<int, Planet> */
    public function getPlanets(): Collection
    {
        return $this->planets;
    }

    public function getBubbleStatus(): PlayerBubbleStatus
    {
        return $this->bubbleStatus;
    }

    public function isInBubble(): bool
    {
        return $this->bubbleStatus->isBubble();
    }

    /**
     * T-150: Tutorial-Phase abgeschlossen. Idempotent.
     */
    public function exitBubble(): void
    {
        $this->bubbleStatus = PlayerBubbleStatus::EXITED;
    }

    public function claimPlanet(Planet $planet): void
    {
        if (!$this->planets->contains($planet)) {
            $this->planets->add($planet);
            $planet->setPlayer($this);
        }
    }

    public function getBackground(): ?PlayerBackground
    {
        return $this->background;
    }

    /**
     * T-122: Background ist permanent. Re-Spec wirft Exception.
     */
    public function setBackground(PlayerBackground $background): void
    {
        if ($this->background !== null) {
            throw new BackgroundAlreadySetException($this->background);
        }
        $this->background = $background;
    }

    public function getStatsBuildingsBuilt(): int
    {
        return $this->statsBuildingsBuilt;
    }

    public function getStatsPlanetsColonized(): int
    {
        return $this->statsPlanetsColonized;
    }

    public function getStatsShipsBuilt(): int
    {
        return $this->statsShipsBuilt;
    }

    /** T-096: Hook für `BuildBuildingCommandService` nach Erfolg. */
    public function recordBuildingBuilt(): void
    {
        ++$this->statsBuildingsBuilt;
    }

    /** T-096: Hook für `ColonizePlanetCommandService` nach Erfolg. */
    public function recordPlanetColonized(): void
    {
        ++$this->statsPlanetsColonized;
    }

    /** T-096: Hook für `BuildShipCommandService` nach Erfolg. */
    public function recordShipBuilt(): void
    {
        ++$this->statsShipsBuilt;
    }
}
