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

    /**
     * T-081: Anti-Crush-Foundation. Wird bei `ClaimStartPlanetCommandService`
     * für den Onboarding-Start-Planet auf `true` gesetzt. Aktiviert in
     * Folge-Tickets (T-081b/T-103/T-080): Pop-Loss-Cap, Resource-Vault,
     * Shield-Cooldown, Abandon-Block (T-101b).
     */
    #[ORM\Column(name: 'is_home_planet', type: 'boolean', options: ['default' => false])]
    private bool $isHomePlanet = false;

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

    public function isHomePlanet(): bool
    {
        return $this->isHomePlanet;
    }

    /**
     * T-081: Markiert diesen Planeten als Heimat-Planet (Anti-Crush-Schutz).
     * Idempotent. Per-Player-Uniqueness wird in `ClaimStartPlanetCommandService`
     * sichergestellt — Domain-Methode selbst kennt keinen Player-Scope.
     */
    public function markAsHome(): void
    {
        $this->isHomePlanet = true;
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
     * T-064b → T-172 Rename: Lokaler Bauzeit-Boost durch fertiges CONSTRUCTION_YARD.
     * Pro Level ×1.10 — multiplikativ mit T-064 Forschung + T-063 Planet-Type-Bonus.
     * Kein Yard → 1.0.
     */
    public function getConstructionYardSpeedMultiplier(?DateTimeImmutable $now = null): float
    {
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::CONSTRUCTION_YARD) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            return pow(1.10, $building->getLevel());
        }

        return 1.0;
    }

    /**
     * T-172: HQ-Level auf diesem Planeten (0 wenn kein HQ — sollte nicht passieren,
     * da ClaimStartPlanet HQ L1 auto-baut). Genutzt für Slot-Cap-Bonus und
     * Forschungs-Prereq-Checks (BuildingLevelPrerequisite).
     */
    public function getHqLevel(?DateTimeImmutable $now = null): int
    {
        foreach ($this->buildings as $building) {
            if ($building->getType() !== BuildingType::HQ) {
                continue;
            }
            if (!$building->isReady($now)) {
                continue;
            }
            return $building->getLevel();
        }

        return 0;
    }

    /**
     * T-172: Effektiver Slot-Cap inkl. HQ-Bonus. PlanetSize bleibt der Hauptfaktor;
     * HQ liefert nur einen kleinen Bonus (capped via PlanetSize.getMaxHQSlotBonus).
     */
    public function getEffectiveBuildingSlotCap(?DateTimeImmutable $now = null): int
    {
        $base = $this->size->getBuildingSlotCap();
        $hqLevel = $this->getHqLevel($now);
        if ($hqLevel <= 1) {
            return $base;
        }
        $bonus = min($hqLevel - 1, $this->size->getMaxHQSlotBonus());

        return $base + $bonus;
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
     * T-171: belegte Building-Slots auf diesem Planeten (Σ getSlotSize() aller
     * existierenden Buildings — egal ob ready oder in Bau).
     */
    public function getBuildingSlotsUsed(): int
    {
        $sum = 0;
        foreach ($this->buildings as $b) {
            $sum += $b->getType()->getSlotSize();
        }

        return $sum;
    }

    public function getBuildingSlotCap(): int
    {
        return $this->size->getBuildingSlotCap();
    }

    /**
     * T-171: prüft ob auf dem Planeten bereits eine Instanz dieses unique-Building-Type
     * existiert. Nicht-unique Buildings: stets false.
     */
    public function hasBuildingOfType(BuildingType $type): bool
    {
        if (!$type->isUnique()) {
            return false;
        }
        foreach ($this->buildings as $b) {
            if ($b->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * T-094c: Effektiver Bau-Queue-Slot-Cap inkl. HQ-Bonus.
     *
     * Foundation-Cap = `BuildBuildingCommandService::MAX_CONCURRENT_BUILDS` (3).
     * HQ-Bonus: +1 pro 5 HQ-Level (L5=+1, L10=+2, L15=+3, ...) bis Hard-Cap.
     *
     * Hard-Cap = 8 — auch HQ L40+ macht keinen Sinn als Parallel-Slot-Quelle,
     * Logistics-Forschung (T-094d) wird der nächste Bonus-Pfad.
     */
    public function getEffectiveBuildQueueCap(?DateTimeImmutable $now = null): int
    {
        $base = 3; // T-094 Foundation
        $hqLevel = $this->getHqLevel($now);
        $bonus = intdiv($hqLevel, 5);

        return min(8, $base + $bonus);
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
     * T-177 Base-Volume ohne Buildings. Ergänzt durch HQ + WAREHOUSE + andere
     * Buildings via `BuildingType::getVolumeContribution()`. Decision Q2=(c).
     *
     * Foundation-Vorschlag aus T-177 sah 50 m³ vor; pragmatisch erhöht auf
     * 5000 m³ damit Onboarding ohne Wand vorm 1. Mine-Tick beginnt (mit Demo-
     * Buff 3000 IRON_ORE = 6000 m³ wäre Cap sonst sofort verletzt). HQ + Warehouse
     * skalieren von dort weiter — Tuning-Knob für späteres Balancing.
     */
    public const BASE_VOLUME_CAPACITY = 5000;

    /**
     * T-177 Generic Volume-Storage. Ersetzt T-061 per-Resource-Cap.
     *
     * `cap = BASE_VOLUME_CAPACITY + Σ(building.type.getVolumeContribution × building.level)`
     *
     * Beispiel: Frischer Planet (HQ L1, WAREHOUSE L1) →
     *   50 (base) + 25 (HQ) + 500 (Warehouse) = 575 m³.
     */
    public function getStorageVolumeCapacity(): int
    {
        $cap = self::BASE_VOLUME_CAPACITY;
        foreach ($this->buildings as $building) {
            $cap += $building->getType()->getVolumeContribution() * $building->getLevel();
        }

        return $cap;
    }

    /**
     * T-177 Live-Sum aller Items × m³-Multi (via `ResourceVolumeConfig`).
     * Pop wird ebenfalls als Volume-Belegung gewertet (T-179-Vorbereitung;
     * Pop wandert in T-179 final in den Storage-Bucket).
     */
    public function getStorageVolumeUsed(): int
    {
        $volume = 0.0;
        foreach ($this->resources as $resource) {
            if ($resource->getAmount() <= 0) {
                continue;
            }
            $volume += $resource->getAmount() * \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($resource->getType());
        }
        $volume += $this->population->getTotal() * \App\Resource\Service\ResourceVolumeConfig::getPopMulti();

        return (int) ceil($volume);
    }

    /**
     * T-177 freier Volume-Platz (clamp ≥ 0).
     */
    public function getStorageVolumeFree(): int
    {
        return max(0, $this->getStorageVolumeCapacity() - $this->getStorageVolumeUsed());
    }

    /**
     * T-177 prüft ob `quantity` weitere Einheiten einer Resource ins Lager passen.
     * Returns max einlegbare Quantity (kann < `quantity` sein bei knappem Platz).
     */
    public function maxAddableQuantity(ResourceType $type, int $quantity): int
    {
        if ($quantity <= 0) {
            return 0;
        }
        $multi = \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($type);
        if ($multi <= 0) {
            return $quantity; // 0-Volume-Items (theoretisch) immer einlegbar
        }
        $free = $this->getStorageVolumeFree();
        $maxByVolume = (int) floor($free / $multi);

        return min($quantity, max(0, $maxByVolume));
    }

    public function canAddItem(ResourceType $type, int $quantity): bool
    {
        return $this->maxAddableQuantity($type, $quantity) >= $quantity;
    }

    /**
     * T-177 deprecated: alte per-Resource-Cap-API für Production-Processors.
     * Liefert die maximale Anzahl der Ressource die der Planet noch fassen
     * könnte gegeben alle anderen Items bereits im Storage sind:
     * `current + floor(volumeFree / multi)`.
     */
    public function getStorageCapacity(ResourceType $resource): int
    {
        $multi = \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($resource);
        if ($multi <= 0) {
            return PHP_INT_MAX;
        }
        $current = 0;
        foreach ($this->resources as $r) {
            if ($r->getType() === $resource) {
                $current = $r->getAmount();
                break;
            }
        }
        $maxAddable = (int) floor($this->getStorageVolumeFree() / $multi);

        return $current + $maxAddable;
    }

    /**
     * T-063: PlanetType-Bonus × Size-Faktor → Effective Mining-Multiplier.
     * Formel: max(0, 1 + typeBonus × sizeFactor). Multipliziert Mining-Output.
     *
     * T-070: Stack mit `CULTURAL_CENTER`-Bonus (+2%/Level, capped +20%).
     */
    public function getEffectiveMiningMultiplier(ResourceType $resource): float
    {
        return $this->effectiveBonus($this->type->getMiningBonus($resource)) * $this->getCulturalCenterMultiplier();
    }

    public function getEffectiveRefinementMultiplier(ResourceType $resource): float
    {
        return $this->effectiveBonus($this->type->getRefinementBonus($resource)) * $this->getCulturalCenterMultiplier();
    }

    /**
     * T-070: Cultural-Center boostet Mining + Refinement um +2%/Level (capped
     * +20%). Idle-Wert ist 1.0 (kein Cultural-Center oder Level 0).
     *
     * Nur fertige Buildings (`finishedAt` null = test-stub) zählen — Pattern
     * analog Pop-Cap-Berechnung.
     */
    public function getCulturalCenterMultiplier(?DateTimeImmutable $now = null): float
    {
        $level = 0;
        foreach ($this->buildings as $building) {
            if ($building->getType() !== \App\Building\ValueObject\BuildingType::CULTURAL_CENTER) {
                continue;
            }
            if ($now !== null && !$building->isReady($now)) {
                continue;
            }
            $level = $building->getLevel();
            break; // CULTURAL_CENTER ist strikt-unique pro Planet (T-070)
        }
        if ($level <= 0) {
            return 1.0;
        }

        return 1.0 + min(0.20, 0.02 * $level);
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
