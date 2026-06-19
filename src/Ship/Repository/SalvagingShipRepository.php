<?php

declare(strict_types=1);

namespace App\Ship\Repository;

use App\Ship\Model\Ship;

/**
 * T-016 Helper-Repo: alle Schiffe mit aktivem Salvage-State.
 * Implementiert nicht als ServiceEntityRepository (das ist ShipRepository),
 * sondern als simpler DQL-Wrapper auf dem em.
 */
final readonly class SalvagingShipRepository
{
    public function __construct(private ShipRepository $shipRepository)
    {
    }

    /**
     * @return list<Ship>
     */
    public function findActiveSalvagers(): array
    {
        return $this->shipRepository->createQueryBuilder('s')
            ->where('s.salvageTargetPoiId IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
