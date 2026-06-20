<?php

declare(strict_types=1);

namespace App\Building\Exception;

use App\Building\ValueObject\BuildingType;
use DomainException;

/**
 * T-171: Versuch einer zweiten Instanz eines strikt-unique Buildings auf demselben
 * Planeten. Spieler soll stattdessen das vorhandene Gebäude upgraden.
 */
class BuildingAlreadyExistsException extends DomainException
{
    public function __construct(BuildingType $type)
    {
        parent::__construct(sprintf(
            'Building %s ist unique pro Planet — verwende Upgrade statt zweite Instanz zu bauen.',
            $type->value,
        ));
    }
}
