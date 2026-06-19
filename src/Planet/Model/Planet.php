<?php

declare(strict_types=1);

namespace App\Planet\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingType;
use App\Common\Doctrine\Type\PlanetIdType;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Planet\ValueObject\PlanetSize;
use App\Planet\ValueObject\PlanetType;
use App\Player\Model\Player;
use App\Resource\Model\Resource;
use App\Resource\Model\ResourceDeposit;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OutOfBoundsException;

#[ORM\Entity(repositoryClass: PlanetRepository::class)]
#[ORM\Table(name: 'planets')]
class Planet
{
    public const BASE_POPULATION_CAP = 100;

    /** @var Collection<int, Building> */
    #[ORM\OneToMany(
        targetEntity: Building::class,
        mappedBy: 'planet',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $buildings;

    /** @var Collection<int, Resource> */
    #[ORM\OneToMany(
        targetEntity: Resource::class,
        mappedBy: 'planet',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $resources;

    /** @var Collection<int, ResourceDeposit> */
    #[ORM\OneToMany(
        targetEntity: ResourceDeposit::class,
        mappedBy: 'planet',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $resourceDeposits;

    #[ORM\Embedded(class: Population::class, columnPrefix: 'population_')]
    private Population $population;

    #[ORM\ManyToOne(targetEntity: SolarSystem::class, inversedBy: 'planets')]
    #[ORM\JoinColumn(name: 'solar_system_id', referencedColumnName: 'id', nullable: true)]
    private ?SolarSystem $solarSystem = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: PlanetIdType::NAME)]
        private PlanetId $id,

        #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'planets')]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: true)]
        private ?Player $player = null,

        #[ORM\Column(type: 'string', length: 32, enumType: PlanetType::class)]
        private PlanetType $type = PlanetType::TERRAN,

        #[ORM\Column(type: 'string', length: 32, enumType: PlanetSize::class)]
        private PlanetSize $size = PlanetSize::MEDIUM,
    ) {
        $this->buildings = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->resourceDeposits = new ArrayCollection();
        $this->population = Population::empty(self::BASE_POPULATION_CAP);
    }

    public static function generatePlanet(
        PlanetId $id,
        PlanetType $type = PlanetType::TERRAN,
        PlanetSize $size = PlanetSize::MEDIUM,
    ): self {
        return new self($id, null, $type, $size);
    }

    public function getId(): PlanetId
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    /** @return Collection<int, Building> */
    public function getBuildings(): Collection
    {
        return $this->buildings;
    }

    public function addBuilding(Building $building, ?DateTimeImmutable $now = null): void
    {
        if (!$this->buildings->contains($building)) {
            $this->buildings->add($building);
            $building->setPlanet($this);
            $this->recalculatePopulationCap($now);
        }
    }

    /**
     * T-062: Counts only `isReady($now)` buildings. With $now=null, behaviour ist legacy:
     * Buildings ohne `finishedAt` zählen, Buildings mit `finishedAt` werden konservativ
     * ausgeschlossen.
     */
    public function recalculatePopulationCap(?DateTimeImmutable $now = null): void
    {
        $bonus = 0;
        foreach ($this->buildings as $building) {
            if (!$building->isReady($now)) {
                continue;
            }
            $bonus += $building->getType()->getPopulationCapBonusPerLevel() * $building->getLevel();
        }
        $this->population->setCap(self::BASE_POPULATION_CAP + $bonus);
    }

    /** @return Collection<int, Resource> */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): void
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setPlanet($this);
        }
    }

    public function getResource(ResourceType $type): Resource
    {
        foreach ($this->resources as $resource) {
            if ($resource->getType() === $type) {
                return $resource;
            }
        }

        throw new OutOfBoundsException(
            sprintf('Resource of type "%s" not present on planet', $type->value)
        );
    }

    public function ensureResource(ResourceType $type): Resource
    {
        foreach ($this->resources as $resource) {
            if ($resource->getType() === $type) {
                return $resource;
            }
        }

        $resource = Resource::generateEmptyResource($type);
        $this->addResource($resource);

        return $resource;
    }

    /** @return Collection<int, ResourceDeposit> */
    public function getResourceDeposits(): Collection
    {
        return $this->resourceDeposits;
    }

    public function addDeposit(ResourceDeposit $deposit): void
    {
        if (!$this->resourceDeposits->contains($deposit)) {
            $this->resourceDeposits->add($deposit);
            $deposit->setPlanet($this);
        }
    }

    public function getResourceDeposit(ResourceType $type): ResourceDeposit
    {
        foreach ($this->resourceDeposits as $deposit) {
            if ($deposit->getResourceType() === $type) {
                return $deposit;
            }
        }

        throw new OutOfBoundsException(
            sprintf('ResourceDeposit of type "%s" not present on planet', $type->value)
        );
    }

    public function getPopulation(): Population
    {
        return $this->population;
    }

    public function getSolarSystem(): ?SolarSystem
    {
        return $this->solarSystem;
    }

    public function setSolarSystem(?SolarSystem $system): void
    {
        $this->solarSystem = $system;
    }

    public function getType(): PlanetType
    {
        return $this->type;
    }

    public function getSize(): PlanetSize
    {
        return $this->size;
    }

    /**
     * T-011: Höchstes Level einer fertiggestellten Raumwerft auf diesem Planeten (0 wenn keine).
     * Voraussetzungs-Check für Schiffsbau (T-012ff). Schiff-Klassen-Mark-Tier wird via T-102/T-128
     * gegen diesen Wert verglichen.
     */
    public function getShipyardLevel(?DateTimeImmutable $now = null): int
    {
        $level = 0;
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::SHIPYARD) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            if ($building->getLevel() > $level) {
                $level = $building->getLevel();
            }
        }

        return $level;
    }

    public function hasShipyard(?DateTimeImmutable $now = null): bool
    {
        return $this->getShipyardLevel($now) > 0;
    }

    /**
     * T-013: Höchstes Level eines fertigen PROBE_LAB auf diesem Planeten (0 wenn keine).
     * Voraussetzungs-Check für Sondenbau.
     */
    public function getProbeLabLevel(?DateTimeImmutable $now = null): int
    {
        $level = 0;
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::PROBE_LAB) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            if ($building->getLevel() > $level) {
                $level = $building->getLevel();
            }
        }

        return $level;
    }

    public function hasProbeLab(?DateTimeImmutable $now = null): bool
    {
        return $this->getProbeLabLevel($now) > 0;
    }

    /**
     * T-018: Höchstes Level eines fertigen TELESCOPE auf diesem Planeten (0 wenn keiner).
     * TelescopeDiscoveryProcessor summiert über alle Planeten des Players.
     */
    public function getTelescopeLevel(?DateTimeImmutable $now = null): int
    {
        $level = 0;
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::TELESCOPE) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            if ($building->getLevel() > $level) {
                $level = $building->getLevel();
            }
        }

        return $level;
    }

    /**
     * T-094 Bau-Queue: Anzahl gerade laufender Bau-/Upgrade-Jobs.
     * Definition: Building.finishedAt > $now (= !isReady). Ein neu gebautes ODER
     * gerade upgradetes Building zählt als aktiver Job.
     */
    public function countActiveBuildJobs(?DateTimeImmutable $now): int
    {
        if ($now === null) {
            return 0;
        }
        $count = 0;
        foreach ($this->buildings as $b) {
            if (!$b->isReady($now)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * T-025: Höchstes Level eines fertigen RESEARCH_LAB auf diesem Planeten (0 wenn keiner).
     * StartResearchCommandService nutzt das maximum über alle Player-Planeten als Speed-Multiplier.
     */
    public function getResearchLabLevel(?DateTimeImmutable $now = null): int
    {
        $level = 0;
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::RESEARCH_LAB) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            if ($building->getLevel() > $level) {
                $level = $building->getLevel();
            }
        }

        return $level;
    }

    /**
     * Live-computed storage capacity for a resource type (T-061).
     * Cap = ResourceCategory base + Σ(building.type.getStorageContribution × building.level)
     */
    public function getStorageCapacity(ResourceType $resource): int
    {
        $cap = $resource->getCategory()->getBaseCap();
        foreach ($this->buildings as $building) {
            $cap += $building->getType()->getStorageContribution($resource) * $building->getLevel();
        }

        return $cap;
    }

    /**
     * T-063: PlanetType-Bonus × Size-Faktor → Effective Mining-Multiplier.
     * Formel: max(0, 1 + typeBonus × sizeFactor). Multipliziert Mining-Output.
     */
    public function getEffectiveMiningMultiplier(ResourceType $resource): float
    {
        return $this->effectiveBonus($this->type->getMiningBonus($resource));
    }

    public function getEffectiveRefinementMultiplier(ResourceType $resource): float
    {
        return $this->effectiveBonus($this->type->getRefinementBonus($resource));
    }

    public function getEffectivePopGrowthMultiplier(): float
    {
        return $this->effectiveBonus($this->type->getPopGrowthBonus());
    }

    /**
     * Construction-Speed: Duration wird durch diesen Multiplier dividiert.
     * Mindest-Multiplier 0.1 (vermeidet Div-by-zero und extreme Speedups).
     */
    public function getEffectiveConstructionSpeedMultiplier(\App\Building\ValueObject\BuildingType $building): float
    {
        $bonus = $this->type->getConstructionSpeedBonus($building);
        $sizeFactor = $this->size->getDepositMultiplier();

        return max(0.1, 1.0 + $bonus * $sizeFactor);
    }

    private function effectiveBonus(float $typeBonus): float
    {
        $sizeFactor = $this->size->getDepositMultiplier();

        return max(0.0, 1.0 + $typeBonus * $sizeFactor);
    }
}
