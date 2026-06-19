<?php

declare(strict_types=1);

namespace App\Faction\Model;

use App\Faction\Repository\PlayerFactionReputationRepository;
use App\Player\Model\Player;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: PlayerFactionReputationRepository::class)]
#[ORM\Table(name: 'player_faction_reputation')]
class PlayerFactionReputation
{
    public const MIN_VALUE = -100;
    public const MAX_VALUE = 100;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: false)]
        private Player $player,

        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Faction::class)]
        #[ORM\JoinColumn(name: 'faction_id', referencedColumnName: 'id', nullable: false)]
        private Faction $faction,

        #[ORM\Column(type: 'integer')]
        private int $value,
    ) {
        $this->assertWithinRange($value);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->assertWithinRange($value);
        $this->value = $value;
    }

    private function assertWithinRange(int $value): void
    {
        if ($value < self::MIN_VALUE || $value > self::MAX_VALUE) {
            throw new InvalidArgumentException(sprintf(
                'Reputation value %d out of range [%d, %d]',
                $value,
                self::MIN_VALUE,
                self::MAX_VALUE,
            ));
        }
    }
}
