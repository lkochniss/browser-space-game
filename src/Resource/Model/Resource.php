<?php

declare(strict_types=1);

namespace App\Resource\Model;

use App\Common\Doctrine\Type\ResourceIdType;
use App\Planet\Model\Planet;
use App\Resource\Repository\ResourceRepository;
use App\Resource\ValueObject\ResourceId;
use App\Resource\ValueObject\ResourceType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\Table(name: 'resources')]
class Resource
{
    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(name: 'planet_id', referencedColumnName: 'id', nullable: true)]
    private ?Planet $planet = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ResourceIdType::NAME)]
        private ResourceId $id,

        #[ORM\Column(type: 'string', length: 32, enumType: ResourceType::class)]
        private ResourceType $type,

        #[ORM\Column(type: 'integer')]
        private int $amount,
    ) {
    }

    public function getId(): ResourceId
    {
        return $this->id;
    }

    public function getType(): ResourceType
    {
        return $this->type;
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

    public static function generateEmptyResource(ResourceType $type): self
    {
        return new self(ResourceId::generate(), $type, 0);
    }

    public static function generateWithAmount(ResourceType $type, int $amount): self
    {
        return new self(ResourceId::generate(), $type, $amount);
    }
}
