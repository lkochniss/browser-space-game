<?php

declare(strict_types=1);

namespace App\Resource\Model;

use App\Common\Doctrine\Type\ResourceDepositIdType;
use App\Planet\Model\Planet;
use App\Resource\Repository\ResourceDepositRepository;
use App\Resource\ValueObject\ResourceDepositId;
use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceDepositRepository::class)]
#[ORM\Table(name: 'resource_deposits')]
class ResourceDeposit
{
    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'resourceDeposits')]
    #[ORM\JoinColumn(name: 'planet_id', referencedColumnName: 'id', nullable: true)]
    private ?Planet $planet = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ResourceDepositIdType::NAME)]
        private ResourceDepositId $id,

        #[ORM\Column(type: 'string', length: 32, enumType: ResourceType::class)]
        private ResourceType $resourceType,

        #[ORM\Column(type: 'integer')]
        private int $amount,
    ) {
    }

    public function getId(): ResourceDepositId
    {
        return $this->id;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getPlanet(): ?Planet
    {
        return $this->planet;
    }

    public function setPlanet(?Planet $planet): void
    {
        $this->planet = $planet;
    }

    public static function generateDepositWithAmount(ResourceType $type, int $amount): self
    {
        return new self(ResourceDepositId::generate(), $type, $amount);
    }
}
