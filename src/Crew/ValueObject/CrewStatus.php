<?php

declare(strict_types=1);

namespace App\Crew\ValueObject;

/**
 * T-104a Lebens-Zyklus eines Crew-Members:
 *  - TRAINING: Akademie produziert, finishedAt in der Zukunft
 *  - IDLE: trainiert + verfügbar, kein Schiff assigned
 *  - ASSIGNED: an einem Schiff aktiv
 *  - DEAD: bei Schiff-Loss ohne Escape-Pod-Survival (T-104a Q4); nicht
 *    mehr im Cap-Counter
 */
enum CrewStatus: string
{
    case TRAINING = 'training';
    case IDLE = 'idle';
    case ASSIGNED = 'assigned';
    case DEAD = 'dead';
}
