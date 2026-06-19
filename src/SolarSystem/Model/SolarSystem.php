<?php

declare(strict_types=1);

namespace App\SolarSystem\Model;

use App\Common\Doctrine\Type\SolarSystemIdType;
use App\Planet\Model\Planet;
use App\SolarSystem\Repository\SolarSystemRepository;
use App\SolarSystem\ValueObject\SolarSystemId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolarSystemRepository::class)]
#[ORM\Table(name: 'solar_systems')]
class SolarSystem
{
    /** @var Collection<int, Planet> */
    #[ORM\OneToMany(
        targetEntity: Planet::class,
        mappedBy: 'solarSystem',
        cascade: ['persist'],
    )]
    private Collection $planets;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: SolarSystemIdType::NAME)]
        private SolarSystemId $id,

        #[ORM\Column(type: 'string', length: 64)]
        private string $name,
    ) {
        $this->planets = new ArrayCollection();
    }

    /**
     * Generates a system with auto-derived name like "Sol-7A3F" from the first 4 hex chars of the UUID.
     */
    public static function generate(SolarSystemId $id): self
    {
        $shortHash = strtoupper(substr(str_replace('-', '', (string) $id), 0, 4));

        return new self($id, sprintf('Sol-%s', $shortHash));
    }

    public function getId(): SolarSystemId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return Collection<int, Planet> */
    public function getPlanets(): Collection
    {
        return $this->planets;
    }

    public function addPlanet(Planet $planet): void
    {
        if (!$this->planets->contains($planet)) {
            $this->planets->add($planet);
            $planet->setSolarSystem($this);
        }
    }
}
