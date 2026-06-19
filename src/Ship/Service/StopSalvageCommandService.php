<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Ship\Exception\ShipNotFoundException;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Ship\ValueObject\ShipId;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-016 Salvage manuell stoppen. Idempotent — ohne aktive Salvage no-op.
 */
readonly class StopSalvageCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShipRepository $shipRepository,
    ) {
    }

    public function __invoke(ShipId $shipId): Ship
    {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipNotFoundException($shipId);
        }

        if ($ship->isSalvaging()) {
            $ship->stopSalvage();
            $this->em->flush();
        }

        return $ship;
    }
}
