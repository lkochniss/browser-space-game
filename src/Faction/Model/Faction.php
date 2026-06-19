<?php

declare(strict_types=1);

namespace App\Faction\Model;

use App\Common\Doctrine\Type\FactionIdType;
use App\Faction\Repository\FactionRepository;
use App\Faction\ValueObject\FactionId;
use App\Faction\ValueObject\FactionType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactionRepository::class)]
#[ORM\Table(name: 'factions')]
#[ORM\UniqueConstraint(name: 'uniq_factions_slug', columns: ['slug'])]
class Faction
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: FactionIdType::NAME)]
        private FactionId $id,

        /**
         * Stable business-key, used for seed/lookup (e.g. "pirate_consortium").
         */
        #[ORM\Column(type: 'string', length: 64)]
        private string $slug,

        #[ORM\Column(type: 'string', length: 128)]
        private string $name,

        #[ORM\Column(type: 'string', length: 32, enumType: FactionType::class)]
        private FactionType $type,

        #[ORM\Column(name: 'is_always_hostile', type: 'boolean')]
        private bool $isAlwaysHostile,

        #[ORM\Column(name: 'default_reputation', type: 'integer')]
        private int $defaultReputation,

        #[ORM\Column(type: 'text')]
        private string $description,
    ) {
    }

    public function getId(): FactionId
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FactionType
    {
        return $this->type;
    }

    public function isAlwaysHostile(): bool
    {
        return $this->isAlwaysHostile;
    }

    public function getDefaultReputation(): int
    {
        return $this->defaultReputation;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
