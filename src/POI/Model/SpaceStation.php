<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\POI\ValueObject\PoiId;
use App\POI\ValueObject\StationStatus;
use App\Player\Model\Player;
use App\Ship\ValueObject\CargoManifest;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-023 Raumstation POI-Subtype. Foundation-Stub:
 *
 * - Eine Station pro SolarSystem (Constraint im Build-Command)
 * - Owner ist optional (nullable für ABANDONED-State, T-023b Übernahme)
 * - Storage: CargoManifest reuse aus T-015 mit groSSer Capacity
 *   (User-Note: Stationen sind Raumdocks mit großem Inventar — keine
 *   Resource-Produktion)
 * - Population auf Station: Pop-Counter; bei 0 + ohne Maintenance → ABANDONED
 *
 * Out-of-Scope (Folge-Tickets):
 * - Maintenance-Tick (Resource-Verbrauch + Pop-Mortality)
 * - Status-Transition ACTIVE → ABANDONED bei Maintenance-Failure
 * - ClaimAbandonedStationCommand für Übernahme
 * - Docking-Slots / Resupply-Mechanik via T-017
 * - Resource-/Erzeugnis-Verteilung Planet ↔ Station via T-015 Cargo
 */
#[ORM\Entity]
class SpaceStation extends Poi
{
    public const DEFAULT_STORAGE_CAPACITY = 100000;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'station_owner_id', referencedColumnName: 'id', nullable: true)]
    private ?Player $owner = null;

    #[ORM\Column(name: 'station_status', type: 'string', length: 16, nullable: true, enumType: StationStatus::class)]
    private ?StationStatus $status = null;

    #[ORM\Column(name: 'station_population', type: 'integer', nullable: true)]
    private ?int $populationOnStation = null;

    #[ORM\Column(name: 'station_storage_capacity', type: 'integer', nullable: true)]
    private ?int $storageCapacity = null;

    #[ORM\Embedded(class: CargoManifest::class, columnPrefix: 'station_cargo_')]
    private CargoManifest $storage;

    public function __construct(
        PoiId $id,
        SolarSystem $solarSystem,
        Player $owner,
        ?string $name = null,
        int $populationOnStation = 200,
        int $storageCapacity = self::DEFAULT_STORAGE_CAPACITY,
    ) {
        parent::__construct($id, $solarSystem, $name);
        $this->owner = $owner;
        $this->status = StationStatus::ACTIVE;
        $this->populationOnStation = $populationOnStation;
        $this->storageCapacity = $storageCapacity;
        $this->storage = CargoManifest::empty();
    }

    public function getOwner(): ?Player
    {
        return $this->owner;
    }

    public function setOwner(?Player $owner): void
    {
        $this->owner = $owner;
    }

    public function getStatus(): StationStatus
    {
        return $this->status ?? StationStatus::ACTIVE;
    }

    public function setStatus(StationStatus $status): void
    {
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === StationStatus::ACTIVE;
    }

    public function isAbandoned(): bool
    {
        return $this->getStatus() === StationStatus::ABANDONED;
    }

    public function getPopulationOnStation(): int
    {
        return $this->populationOnStation ?? 0;
    }

    public function setPopulationOnStation(int $pop): void
    {
        $this->populationOnStation = max(0, $pop);
    }

    public function getStorageCapacity(): int
    {
        return $this->storageCapacity ?? 0;
    }

    public function getStorage(): CargoManifest
    {
        return $this->storage;
    }

    public function getStorageFreeUnits(): int
    {
        return $this->getStorageCapacity() - $this->storage->getTotalUnits();
    }
}
