<?php

declare(strict_types=1);

namespace App\POI\Model;

use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * T-022 Nebel POI-Subtype. Foundation-Stub mit `concealmentLevel` (1-10).
 *
 * Effekt: Flotten/Sonden im Nebel sind verborgen. Detection-Logic wird in
 * Folge-Tickets integriert:
 * - T-074 Pirate-Encounter-Spawn: NPC ignoriert Player-Flotten im Nebel
 * - T-103 Battle-Resolution: Battle-Stat-Modifier (z.B. -10% Damage durch
 *   schlechte Sicht)
 * - T-018 Teleskop-Discovery / T-087 Fog-of-War: Inhalt des Nebels ist nicht
 *   passiv erkennbar, nur durch Anflug eines eigenen Schiffs
 *
 * T-022 selbst hält nur die statischen Stats. Keine Fleet-/Detection-Hooks.
 */
#[ORM\Entity]
class Nebula extends Poi
{
    /**
     * Stealth-Stat. Höher = stärkere Verbergung.
     */
    #[ORM\Column(name: 'nebula_concealment', type: 'integer', nullable: true)]
    private ?int $concealmentLevel = null;

    public function __construct(
        PoiId $id,
        SolarSystem $solarSystem,
        ?string $name = null,
        int $concealmentLevel = 5,
    ) {
        parent::__construct($id, $solarSystem, $name);
        $this->setConcealmentLevel($concealmentLevel);
    }

    public function getConcealmentLevel(): int
    {
        return $this->concealmentLevel ?? 5;
    }

    public function setConcealmentLevel(int $level): void
    {
        if ($level < 1 || $level > 10) {
            throw new InvalidArgumentException(sprintf(
                'Nebula concealmentLevel must be in [1, 10], got %d',
                $level,
            ));
        }
        $this->concealmentLevel = $level;
    }
}
