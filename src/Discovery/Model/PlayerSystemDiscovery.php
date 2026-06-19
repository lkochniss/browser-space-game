<?php

declare(strict_types=1);

namespace App\Discovery\Model;

use App\Common\Doctrine\Type\DiscoveryIdType;
use App\Discovery\Repository\PlayerSystemDiscoveryRepository;
use App\Discovery\ValueObject\DiscoveryId;
use App\Player\Model\Player;
use App\SolarSystem\Model\SolarSystem;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-018 Foundation: Player kennt SolarSystem ja/nein. Single-Boolean-Marker
 * (existence = discovered). T-087 Fog-of-War erweitert später um Tier-Levels
 * + POI-Discovery.
 */
#[ORM\Entity(repositoryClass: PlayerSystemDiscoveryRepository::class)]
#[ORM\Table(name: 'player_system_discoveries')]
#[ORM\UniqueConstraint(name: 'uniq_player_system', columns: ['player_id', 'solar_system_id'])]
class PlayerSystemDiscovery
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: DiscoveryIdType::NAME)]
        private DiscoveryId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: false)]
        private Player $player,

        #[ORM\ManyToOne(targetEntity: SolarSystem::class)]
        #[ORM\JoinColumn(name: 'solar_system_id', referencedColumnName: 'id', nullable: false)]
        private SolarSystem $solarSystem,

        #[ORM\Column(type: 'datetime_immutable')]
        private DateTimeImmutable $discoveredAt,
    ) {
    }

    public static function generate(Player $player, SolarSystem $system, DateTimeImmutable $now): self
    {
        return new self(DiscoveryId::generate(), $player, $system, $now);
    }

    public function getId(): DiscoveryId
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getSolarSystem(): SolarSystem
    {
        return $this->solarSystem;
    }

    public function getDiscoveredAt(): DateTimeImmutable
    {
        return $this->discoveredAt;
    }
}
