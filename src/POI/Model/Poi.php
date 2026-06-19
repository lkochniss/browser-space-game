<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\Common\Doctrine\Type\PoiIdType;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-019 POI-Foundation mit Single-Table-Inheritance.
 *
 * - DiscriminatorColumn `type` mappt auf PoiType-Enum-Werte.
 * - DiscriminatorMap initial: alle 7 Werte → Poi::class (Foundation-Stub).
 * - Folge-Tickets (T-020 Asteroidenfeld, T-021 Trümmerfeld, T-022 Nebel, T-023
 *   Raumstation, T-085 Wurmloch, T-086 Schwarzes Loch, T-074 Pirat-Flotten)
 *   erweitern die Map mit eigenen Subklassen.
 *
 * Discovery-State (welcher Player kennt welches POI) ist Out-of-Scope — kommt
 * mit T-087 Fog-of-War als separate PlayerSystemDiscovery-Entity.
 */
#[ORM\Entity(repositoryClass: PoiRepository::class)]
#[ORM\Table(name: 'pois')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 32)]
#[ORM\DiscriminatorMap([
    'debris_field' => DebrisField::class,
    'nebula' => Nebula::class,
    'station' => SpaceStation::class,
    'unknown_fleet' => Poi::class,
    'asteroid_field' => AsteroidField::class,
    'wormhole' => Wormhole::class,
    'black_hole' => Poi::class,
])]
class Poi
{
    #[ORM\ManyToOne(targetEntity: SolarSystem::class, inversedBy: 'pois')]
    #[ORM\JoinColumn(name: 'solar_system_id', referencedColumnName: 'id', nullable: false)]
    private SolarSystem $solarSystem;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: PoiIdType::NAME)]
        private PoiId $id,

        SolarSystem $solarSystem,

        #[ORM\Column(name: 'name', type: 'string', length: 64, nullable: true)]
        private ?string $name = null,
    ) {
        $this->solarSystem = $solarSystem;
    }

    public function getId(): PoiId
    {
        return $this->id;
    }

    public function getSolarSystem(): SolarSystem
    {
        return $this->solarSystem;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
