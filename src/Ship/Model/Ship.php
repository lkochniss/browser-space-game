<?php

declare(strict_types=1);

namespace App\Ship\Model;

use App\Common\Doctrine\Type\ShipIdType;
use App\Fleet\Model\Fleet;
use App\Planet\Model\Planet;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\CargoManifest;
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
     * T-015: Cargo-Manifest. Bei non-Transport-Schiffen leer + cargoCapacity=0 → Hard-Reject
     * jeglicher Lade-Operation.
     */
    #[ORM\Embedded(class: CargoManifest::class, columnPrefix: 'cargo_')]
    private CargoManifest $cargo;

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
         * T-015 Cargo-Slots. 0 = nicht-Transport-Schiff, lädt nichts.
         * Wert wird beim Build via ShipCostConfig::getCargoCapacity gesetzt.
         */
        #[ORM\Column(name: 'cargo_capacity', type: 'integer')]
        private int $cargoCapacity = 0,
    ) {
        $this->cargo = CargoManifest::empty();
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
        return $this->planet !== null;
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

    public function getCargo(): CargoManifest
    {
        return $this->cargo;
    }

    public function getCargoCapacity(): int
    {
        return $this->cargoCapacity;
    }

    public function getCargoFreeUnits(): int
    {
        return $this->cargoCapacity - $this->cargo->getTotalUnits();
    }

    public function isTransport(): bool
    {
        return $this->type->isTransport();
    }

    public function loadResourceCargo(ResourceType $type, int $amount): void
    {
        if ($amount > $this->getCargoFreeUnits()) {
            throw new \DomainException(sprintf(
                'Cargo capacity exceeded: trying to load %d units, only %d free',
                $amount,
                $this->getCargoFreeUnits(),
            ));
        }
        $this->cargo->loadResource($type, $amount);
    }

    public function unloadResourceCargo(ResourceType $type, int $amount): void
    {
        $this->cargo->unloadResource($type, $amount);
    }

    public function loadPopCargo(int $amount): void
    {
        if ($amount > $this->getCargoFreeUnits()) {
            throw new \DomainException(sprintf(
                'Cargo capacity exceeded: trying to load %d pop, only %d free',
                $amount,
                $this->getCargoFreeUnits(),
            ));
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
}
