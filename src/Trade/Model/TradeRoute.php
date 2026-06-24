<?php

declare(strict_types=1);

namespace App\Trade\Model;

use App\Common\Doctrine\Type\TradeRouteIdType;
use App\Planet\Model\Planet;
use App\Player\Model\Player;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Trade\Repository\TradeRouteRepository;
use App\Trade\ValueObject\TradeRouteId;
use App\Trade\ValueObject\TradeRouteLeg;
use App\Trade\ValueObject\TradeRouteStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-110 TradeRoute: bindet ein Schiff an eine Auto-Transport-Schleife
 * zwischen zwei eigenen Planeten. Fixed-Route loopt source→target→source
 * mit konfigurierbarer Outbound + Return-Cargo; Single-Trip endet nach
 * dem ersten Outbound-Unload.
 *
 * `currentLeg` ist der State-Machine-Marker für `TradeRouteProcessor`.
 */
#[ORM\Entity(repositoryClass: TradeRouteRepository::class)]
#[ORM\Table(name: 'trade_routes')]
class TradeRoute
{
    #[ORM\Column(name: 'outbound_resource', type: 'string', length: 32, enumType: ResourceType::class)]
    private ResourceType $outboundResource;

    #[ORM\Column(name: 'outbound_qty', type: 'integer')]
    private int $outboundQty;

    #[ORM\Column(name: 'return_resource', type: 'string', length: 32, enumType: ResourceType::class, nullable: true)]
    private ?ResourceType $returnResource = null;

    #[ORM\Column(name: 'return_qty', type: 'integer', nullable: true)]
    private ?int $returnQty = null;

    #[ORM\Column(name: 'status', type: 'string', length: 16, enumType: TradeRouteStatus::class)]
    private TradeRouteStatus $status = TradeRouteStatus::ACTIVE;

    #[ORM\Column(name: 'current_leg', type: 'string', length: 32, enumType: TradeRouteLeg::class)]
    private TradeRouteLeg $currentLeg = TradeRouteLeg::AT_SOURCE;

    #[ORM\Column(name: 'last_trip_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastTripAt = null;

    #[ORM\Column(name: 'trip_counter', type: 'integer', options: ['default' => 0])]
    private int $tripCounter = 0;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: TradeRouteIdType::NAME)]
        private TradeRouteId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
        private Player $owner,

        #[ORM\ManyToOne(targetEntity: Planet::class)]
        #[ORM\JoinColumn(name: 'source_planet_id', referencedColumnName: 'id', nullable: false)]
        private Planet $sourcePlanet,

        #[ORM\ManyToOne(targetEntity: Planet::class)]
        #[ORM\JoinColumn(name: 'target_planet_id', referencedColumnName: 'id', nullable: false)]
        private Planet $targetPlanet,

        #[ORM\ManyToOne(targetEntity: Ship::class)]
        #[ORM\JoinColumn(name: 'bound_ship_id', referencedColumnName: 'id', nullable: false)]
        private Ship $boundShip,
    ) {
    }

    public static function createFixed(
        TradeRouteId $id,
        Player $owner,
        Planet $source,
        Planet $target,
        Ship $ship,
        ResourceType $outboundResource,
        int $outboundQty,
        ?ResourceType $returnResource = null,
        ?int $returnQty = null,
    ): self {
        $route = new self($id, $owner, $source, $target, $ship);
        $route->outboundResource = $outboundResource;
        $route->outboundQty = $outboundQty;
        $route->returnResource = $returnResource;
        $route->returnQty = $returnQty;
        $route->status = TradeRouteStatus::ACTIVE;

        return $route;
    }

    public static function createSingleTrip(
        TradeRouteId $id,
        Player $owner,
        Planet $source,
        Planet $target,
        Ship $ship,
        ResourceType $resource,
        int $qty,
    ): self {
        $route = new self($id, $owner, $source, $target, $ship);
        $route->outboundResource = $resource;
        $route->outboundQty = $qty;
        $route->returnResource = null;
        $route->returnQty = null;
        $route->status = TradeRouteStatus::SINGLE_TRIP;

        return $route;
    }

    public function getId(): TradeRouteId
    {
        return $this->id;
    }

    public function getOwner(): Player
    {
        return $this->owner;
    }

    public function getSourcePlanet(): Planet
    {
        return $this->sourcePlanet;
    }

    public function getTargetPlanet(): Planet
    {
        return $this->targetPlanet;
    }

    public function getBoundShip(): Ship
    {
        return $this->boundShip;
    }

    public function getOutboundResource(): ResourceType
    {
        return $this->outboundResource;
    }

    public function getOutboundQty(): int
    {
        return $this->outboundQty;
    }

    public function getReturnResource(): ?ResourceType
    {
        return $this->returnResource;
    }

    public function getReturnQty(): ?int
    {
        return $this->returnQty;
    }

    public function hasReturn(): bool
    {
        return $this->returnResource !== null && $this->returnQty !== null;
    }

    public function getStatus(): TradeRouteStatus
    {
        return $this->status;
    }

    public function getCurrentLeg(): TradeRouteLeg
    {
        return $this->currentLeg;
    }

    public function getLastTripAt(): ?DateTimeImmutable
    {
        return $this->lastTripAt;
    }

    public function getTripCounter(): int
    {
        return $this->tripCounter;
    }

    public function setLeg(TradeRouteLeg $leg): void
    {
        $this->currentLeg = $leg;
    }

    public function pause(): void
    {
        if ($this->status === TradeRouteStatus::CANCELLED) {
            return;
        }
        $this->status = TradeRouteStatus::PAUSED;
    }

    public function resume(): void
    {
        if ($this->status !== TradeRouteStatus::PAUSED) {
            return;
        }
        $this->status = $this->hasReturn() || $this->status === TradeRouteStatus::SINGLE_TRIP
            ? TradeRouteStatus::ACTIVE
            : TradeRouteStatus::ACTIVE;
    }

    public function cancel(): void
    {
        $this->status = TradeRouteStatus::CANCELLED;
    }

    public function recordCompletedLeg(DateTimeImmutable $now): void
    {
        $this->lastTripAt = $now;
        ++$this->tripCounter;
    }
}
