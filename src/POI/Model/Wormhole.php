<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;

/**
 * T-085 Wurmloch POI-Subtype. Foundation-Stub für Inter-System-Travel-Shortcut.
 *
 * Wormholes existieren als Pair: A in System-X, B in System-Y. Beide POIs
 * referenzieren sich gegenseitig via `twin`. Beim Travel von Schiff A nach B
 * (T-017 Movement) prüft Service ob beide Endpunkte Wormholes sind und nutzt
 * Shortcut-Logic (geringere Travel-Time).
 *
 * Foundation T-085:
 * - Pair-Verlinkung (twin: ?Wormhole — bidirektional)
 * - Galaxy-Generation seedet 0-1 Pair pro Galaxy
 * - Tech-Lock (`requiredTechSlug`) als String-Stub für T-026 FTL-Tier-2
 *
 * Out-of-Scope: Travel-Time-Reduktion via Wormhole, Cooldown, Treibstoff-
 * Multiplier, Discovery — kommt mit T-017-Erweiterung + T-026 + T-087.
 */
#[ORM\Entity]
class Wormhole extends Poi
{
    #[ORM\OneToOne(targetEntity: Wormhole::class)]
    #[ORM\JoinColumn(name: 'wormhole_twin_id', referencedColumnName: 'id', nullable: true)]
    private ?Wormhole $twin = null;

    /**
     * Slug des Tech-Eintrags der für Transit notwendig ist (T-026 FTL-Tier-2).
     * NULL = kein Tech-Lock (Foundation default).
     */
    #[ORM\Column(name: 'wormhole_tech_slug', type: 'string', length: 64, nullable: true)]
    private ?string $requiredTechSlug = null;

    public function __construct(
        PoiId $id,
        SolarSystem $solarSystem,
        ?string $name = null,
        ?string $requiredTechSlug = null,
    ) {
        parent::__construct($id, $solarSystem, $name);
        $this->requiredTechSlug = $requiredTechSlug;
    }

    public function getTwin(): ?Wormhole
    {
        return $this->twin;
    }

    /**
     * Bidirektionales Pairing. Setzt das Twin auf beiden Seiten.
     * Idempotent — kein Effekt wenn schon korrekt verlinkt.
     */
    public function pairWith(Wormhole $other): void
    {
        if ($this->twin === $other && $other->twin === $this) {
            return;
        }
        $this->twin = $other;
        $other->twin = $this;
    }

    public function getRequiredTechSlug(): ?string
    {
        return $this->requiredTechSlug;
    }

    public function setRequiredTechSlug(?string $slug): void
    {
        $this->requiredTechSlug = $slug;
    }
}
