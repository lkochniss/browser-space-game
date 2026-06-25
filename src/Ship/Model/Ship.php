<?php

declare(strict_types=1);

namespace App\Ship\Model;

use App\Common\Doctrine\Type\ShipIdType;
use App\Fleet\Model\Fleet;
use App\POI\Model\SpaceStation;
use App\Planet\Model\Planet;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Exception\ShipCargoOverflowException;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\PropulsionType;
use App\Ship\ValueObject\ShipCargo;
use App\Ship\ValueObject\ShipClass;
use App\Ship\ValueObject\ShipId;
use App\Ship\ValueObject\ShipType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipRepository::class)]
#[ORM\Table(name: 'ships')]
class Ship
{
    /**
     * Wo das Schiff aktuell ist. NULL = unterwegs (T-017 Flotte-Movement).
     * In T-012 ist der Wert nach Build immer der Build-Planet (= Heimat).
     */
    #[ORM\ManyToOne(targetEntity: Planet::class)]
    #[ORM\JoinColumn(name: 'planet_id', referencedColumnName: 'id', nullable: true)]
    private ?Planet $planet = null;

    /**
     * T-015b: Schiff kann an Planet ODER an SpaceStation docken (XOR — beide
     * gleichzeitig nicht zulässig). NULL = nicht an Station; siehe `dockAtStation`.
     */
    #[ORM\ManyToOne(targetEntity: SpaceStation::class)]
    #[ORM\JoinColumn(name: 'station_id', referencedColumnName: 'id', nullable: true)]
    private ?SpaceStation $station = null;

    /**
     * Wallclock-Build wie bei Buildings (T-062). NULL = sofort fertig (Test-Fixture).
     */
    #[ORM\Column(name: 'finished_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $finishedAt = null;

    /**
     * T-017: Schiff in genau einer Fleet (Doc-Vorgabe). NULL = nicht in Fleet
     * (frisch gebaut, noch nicht zugewiesen).
     */
    #[ORM\ManyToOne(targetEntity: Fleet::class, inversedBy: 'ships')]
    #[ORM\JoinColumn(name: 'fleet_id', referencedColumnName: 'id', nullable: true)]
    private ?Fleet $fleet = null;

    /**
     * T-178: Cargo (volume-based). Jedes Schiff hat Cargo, Capacity über
     * `cargoVolumeCapacity` (m³). Items belegen Volume via `ResourceVolumeConfig`.
     */
    #[ORM\Embedded(class: ShipCargo::class, columnPrefix: 'cargo_')]
    private ShipCargo $cargo;

    /**
     * T-016 Salvage-Action State (nur SALVAGE-Schiffe).
     * NULL = kein Salvage aktiv.
     * Pro Tick rechnet SalvageProcessor `now - salvage_last_tick_at` × Rate
     * und extrahiert aus dem Target-POI in den Cargo. Stop-Conditions: Field
     * leer ODER Schiff-Cargo voll → State wird gecleart.
     */
    #[ORM\Column(name: 'salvage_target_poi_id', type: 'string', length: 36, nullable: true)]
    private ?string $salvageTargetPoiId = null;

    #[ORM\Column(name: 'salvage_resource_type', type: 'string', length: 32, nullable: true)]
    private ?string $salvageResourceType = null;

    #[ORM\Column(name: 'salvage_last_tick_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $salvageLastTickAt = null;

    /**
     * T-102 Combat-Klasse. NULL für non-combat (Spezial-Schiffe via ShipType).
     * Wenn gesetzt liefert `ShipBlueprintRegistry` Stats (hp/damage/shield/etc).
     */
    #[ORM\Column(name: 'ship_class', type: 'string', length: 32, enumType: ShipClass::class, nullable: true)]
    private ?ShipClass $shipClass = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ShipIdType::NAME)]
        private ShipId $id,

        #[ORM\Column(type: 'string', length: 32, enumType: ShipType::class)]
        private ShipType $type,

        /**
         * Pop-Slots, die durch dieses Schiff dauerhaft auf seinem Planet
         * gebunden sind. Wird bei Schiff-Death komplett verloren (T-012-Decision).
         */
        #[ORM\Column(name: 'population_assigned', type: 'integer')]
        private int $populationAssigned,

        #[ORM\Column(name: 'supply_water', type: 'integer')]
        private int $supplyWater = 0,

        #[ORM\Column(name: 'supply_food', type: 'integer')]
        private int $supplyFood = 0,

        #[ORM\Column(name: 'supply_oxygen', type: 'integer')]
        private int $supplyOxygen = 0,

        #[ORM\Column(name: 'supply_capacity', type: 'integer')]
        private int $supplyCapacity = self::DEFAULT_SUPPLY_CAPACITY,

        /**
         * T-178 Cargo-Volume-Cap (m³). Wird beim Build via
         * `ShipCargoVolumeConfig::getCargoVolume` gesetzt — jedes Schiff hat
         * jetzt Cargo (auch Non-Transport), nur unterschiedliche Größen.
         */
        #[ORM\Column(name: 'cargo_volume_capacity', type: 'integer')]
        private int $cargoVolumeCapacity = 0,

        /**
         * T-026c: Antriebs-Typ pro Schiff. HYDROGEN ist Foundation-Default
         * (kein Research nötig); andere brauchen entsprechende Forschung beim
         * Build via `PropulsionType::getRequiredResearchSlug()`.
         */
        #[ORM\Column(name: 'propulsion', type: 'string', length: 16, enumType: PropulsionType::class)]
        private PropulsionType $propulsion = PropulsionType::HYDROGEN,
    ) {
        $this->cargo = ShipCargo::empty();
    }

    public function getPropulsion(): PropulsionType
    {
        return $this->propulsion;
    }

    /**
     * T-026c: Effektive Speed = ShipType.getSpeed × Propulsion.getSpeedMultiplier.
     * Wird in `Fleet::getMinSpeed()` für die Fleet-Travel-Berechnung genutzt.
     */
    public function getEffectiveSpeed(): float
    {
        return $this->type->getSpeed() * $this->propulsion->getSpeedMultiplier();
    }

    public const DEFAULT_SUPPLY_CAPACITY = 30;

    public function getId(): ShipId
    {
        return $this->id;
    }

    public function getType(): ShipType
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

    public function isDocked(): bool
    {
        return $this->planet !== null || $this->station !== null;
    }

    public function getStation(): ?SpaceStation
    {
        return $this->station;
    }

    /**
     * T-015b: Wechselt Dock von Planet → Station. Setzt planet=null + station=$station.
     * `null` = undock (Schiff in Transit).
     */
    public function setStation(?SpaceStation $station): void
    {
        $this->station = $station;
        if ($station !== null) {
            $this->planet = null;
        }
    }

    public function getPopulationAssigned(): int
    {
        return $this->populationAssigned;
    }

    public function getSupplyWater(): int
    {
        return $this->supplyWater;
    }

    public function getSupplyFood(): int
    {
        return $this->supplyFood;
    }

    public function getSupplyOxygen(): int
    {
        return $this->supplyOxygen;
    }

    public function getSupplyCapacity(): int
    {
        return $this->supplyCapacity;
    }

    public function setSupplies(int $water, int $food, int $oxygen): void
    {
        $this->supplyWater = max(0, min($this->supplyCapacity, $water));
        $this->supplyFood = max(0, min($this->supplyCapacity, $food));
        $this->supplyOxygen = max(0, min($this->supplyCapacity, $oxygen));
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

    public function getCargo(): ShipCargo
    {
        return $this->cargo;
    }

    public function getCargoVolumeCapacity(): int
    {
        return $this->cargoVolumeCapacity;
    }

    public function getCargoVolumeUsed(): int
    {
        return $this->cargo->usedVolume();
    }

    public function getCargoVolumeFree(): int
    {
        return max(0, $this->cargoVolumeCapacity - $this->getCargoVolumeUsed());
    }

    public function isTransport(): bool
    {
        return $this->type->isTransport();
    }

    /**
     * T-178 Max einlegbare Resource-Quantity gegeben aktueller Volume-Belegung.
     * Analog `Planet::maxAddableQuantity`.
     */
    public function maxAddableResource(ResourceType $type, int $quantity): int
    {
        if ($quantity <= 0) {
            return 0;
        }
        $multi = \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($type);
        if ($multi <= 0) {
            return $quantity;
        }
        $maxByVolume = (int) floor($this->getCargoVolumeFree() / $multi);

        return min($quantity, max(0, $maxByVolume));
    }

    /**
     * T-178 Max einlegbare Pop-Anzahl.
     */
    public function maxAddablePop(int $quantity): int
    {
        if ($quantity <= 0) {
            return 0;
        }
        $multi = \App\Resource\Service\ResourceVolumeConfig::getPopMulti();
        $maxByVolume = (int) floor($this->getCargoVolumeFree() / $multi);

        return min($quantity, max(0, $maxByVolume));
    }

    /**
     * T-178 Volume-Cap-Check für Resource-Load: berechnet m³ aus
     * `amount × ResourceVolumeConfig::getMultiForResource`.
     */
    public function canAddResource(ResourceType $type, int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }
        $needed = (int) ceil(
            $amount * \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($type),
        );

        return $needed <= $this->getCargoVolumeFree();
    }

    /**
     * T-178 Volume-Cap-Check für Pop-Load (Pop-Multi = 10 m³/Person).
     */
    public function canAddPop(int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }
        $needed = (int) ceil(
            $amount * \App\Resource\Service\ResourceVolumeConfig::getPopMulti(),
        );

        return $needed <= $this->getCargoVolumeFree();
    }

    public function loadResourceCargo(ResourceType $type, int $amount): void
    {
        if (!$this->canAddResource($type, $amount)) {
            $needed = (int) ceil(
                $amount * \App\Resource\Service\ResourceVolumeConfig::getMultiForResource($type),
            );
            throw new ShipCargoOverflowException($this->id, $needed, $this->getCargoVolumeFree());
        }
        $this->cargo->loadResource($type, $amount);
    }

    public function unloadResourceCargo(ResourceType $type, int $amount): void
    {
        $this->cargo->unloadResource($type, $amount);
    }

    public function loadPopCargo(int $amount): void
    {
        if (!$this->canAddPop($amount)) {
            $needed = (int) ceil(
                $amount * \App\Resource\Service\ResourceVolumeConfig::getPopMulti(),
            );
            throw new ShipCargoOverflowException($this->id, $needed, $this->getCargoVolumeFree());
        }
        $this->cargo->loadPop($amount);
    }

    public function unloadPopCargo(int $amount): void
    {
        $this->cargo->unloadPop($amount);
    }

    public function getFleet(): ?Fleet
    {
        return $this->fleet;
    }

    public function setFleet(?Fleet $fleet): void
    {
        $this->fleet = $fleet;
    }

    public function getSalvageTargetPoiId(): ?string
    {
        return $this->salvageTargetPoiId;
    }

    public function getSalvageResourceType(): ?ResourceType
    {
        if ($this->salvageResourceType === null) {
            return null;
        }

        return ResourceType::from($this->salvageResourceType);
    }

    public function getSalvageLastTickAt(): ?DateTimeImmutable
    {
        return $this->salvageLastTickAt;
    }

    public function isSalvaging(): bool
    {
        return $this->salvageTargetPoiId !== null;
    }

    public function startSalvage(string $poiId, ResourceType $resource, DateTimeImmutable $now): void
    {
        $this->salvageTargetPoiId = $poiId;
        $this->salvageResourceType = $resource->value;
        $this->salvageLastTickAt = $now;
    }

    public function updateSalvageTick(DateTimeImmutable $now): void
    {
        $this->salvageLastTickAt = $now;
    }

    public function stopSalvage(): void
    {
        $this->salvageTargetPoiId = null;
        $this->salvageResourceType = null;
        $this->salvageLastTickAt = null;
    }

    public function getShipClass(): ?ShipClass
    {
        return $this->shipClass;
    }

    public function setShipClass(?ShipClass $class): void
    {
        $this->shipClass = $class;
    }

    public function isCombatShip(): bool
    {
        return $this->shipClass !== null;
    }

    /**
     * T-102 + T-104a Captain-Permadeath. Prefers ShipClass-Pod-Chance (Combat)
     * über ShipType-Stub (legacy = 0 für alle existing types).
     */
    public function getEscapePodSurvivalChance(): int
    {
        if ($this->shipClass !== null) {
            return $this->shipClass->getEscapePodSurvivalChance();
        }

        return $this->type->getEscapePodSurvivalChance();
    }

    /**
     * T-103 Battle-Engine HP-State. NULL = "noch nie im Battle", BattleResolver
     * resetted das via Initial-Stats-Lookup. Damage in Battle persistiert
     * zwischen Rounds; Schiff stirbt bei HP <= 0.
     */
    #[ORM\Column(name: 'battle_current_hp', type: 'integer', nullable: true)]
    private ?int $battleCurrentHp = null;

    public function getBattleCurrentHp(): ?int
    {
        return $this->battleCurrentHp;
    }

    public function setBattleCurrentHp(?int $hp): void
    {
        $this->battleCurrentHp = $hp === null ? null : max(0, $hp);
    }
}
