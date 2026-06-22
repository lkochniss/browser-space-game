<?php

declare(strict_types=1);

namespace App\Player\Model;

use App\Common\Doctrine\Type\PlayerIdType;
use App\Planet\Model\Planet;
use App\Player\Repository\PlayerRepository;
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
}
