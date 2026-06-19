<?php

declare(strict_types=1);

namespace App\Research\Model;

use App\Common\Doctrine\Type\ResearchIdType;
use App\Player\Model\Player;
use App\Research\Repository\PlayerResearchRepository;
use App\Research\ValueObject\ResearchId;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-025: persistierter Forschungsstand pro Player + Node-Slug.
 *
 * Existence eines Eintrags + level > 0 = "schon erforscht (auf Level X)".
 * UNIQUE(player_id, node_slug) verhindert Duplikate.
 */
#[ORM\Entity(repositoryClass: PlayerResearchRepository::class)]
#[ORM\Table(name: 'player_research')]
#[ORM\UniqueConstraint(name: 'uniq_player_node', columns: ['player_id', 'node_slug'])]
class PlayerResearch
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: ResearchIdType::NAME)]
        private ResearchId $id,

        #[ORM\ManyToOne(targetEntity: Player::class)]
        #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: false)]
        private Player $player,

        #[ORM\Column(name: 'node_slug', type: 'string', length: 64)]
        private string $nodeSlug,

        #[ORM\Column(type: 'integer')]
        private int $level,
    ) {
    }

    public static function generate(Player $player, string $nodeSlug, int $level = 1): self
    {
        return new self(ResearchId::generate(), $player, $nodeSlug, $level);
    }

    public function getId(): ResearchId
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getNodeSlug(): string
    {
        return $this->nodeSlug;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function incrementLevel(): void
    {
        $this->level++;
    }
}
