<?php

declare(strict_types=1);

namespace App\Resource\ValueObject;

enum ResourceCategory: string
{
    /** Endlich, abgebaut aus Vorkommen */
    case FINITE = 'finite';

    /** Erneuerbar, ohne Vorkommen, Pop-abhängig */
    case RENEWABLE = 'renewable';

    /** Veredelt aus Rohstoffen via Verarbeitungs-Building */
    case REFINED = 'refined';

    /**
     * Per-Planet Base-Storage-Cap. Building-Contributions stack on top (T-061).
     * - Renewables haben grosses natural buffer (Land/Atmosphäre)
     * - Endliche Erze haben kleinen natural buffer → Storage-Bau notwendig
     * - Erzeugnisse haben kleinen natural buffer (industrielles Lager nötig)
     */
    public function getBaseCap(): int
    {
        return match ($this) {
            self::RENEWABLE => 500,
            self::FINITE => 100,
            self::REFINED => 100,
        };
    }
}
