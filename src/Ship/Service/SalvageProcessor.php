<?php

declare(strict_types=1);

namespace App\Ship\Service;

use App\Common\Interface\ClockInterface;
use App\POI\Model\AsteroidField;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\Repository\SalvagingShipRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-016 Tick-Service. Globaler Service (kein TickProcessorInterface, da nicht
 * Planet-zentriert). T-044 Tick-Scheduler ruft `runTick()` via Cron.
 *
 * Pro aktivem Salvage-Schiff:
 * - Berechne `delta = now - salvageLastTickAt` (in Minuten)
 * - `extractable = floor(delta × ratePerMinute)`
 * - Limitiere durch verfügbares Field-Amount + Schiff-Cargo-Free
 * - Field.extract + Ship.loadResourceCargo
 * - Update salvageLastTickAt = now
 * - Stop-Conditions: Field empty ODER Cargo voll → ship.stopSalvage()
 * - Field-Cleanup: wenn AsteroidField.isEmpty() → em->remove(field)
 */
readonly class SalvageProcessor
{
    public function __construct(
        private EntityManagerInterface $em,
        private SalvagingShipRepository $salvagingRepo,
        private PoiRepository $poiRepository,
        private ClockInterface $clock,
    ) {
    }

    public function runTick(): int
    {
        $now = $this->clock->now();
        $processed = 0;

        foreach ($this->salvagingRepo->findActiveSalvagers() as $ship) {
            if ($this->processShip($ship, $now)) {
                $processed++;
            }
        }

        $this->em->flush();

        return $processed;
    }

    private function processShip(Ship $ship, \DateTimeImmutable $now): bool
    {
        $targetIdStr = $ship->getSalvageTargetPoiId();
        $resource = $ship->getSalvageResourceType();
        $lastTick = $ship->getSalvageLastTickAt();

        if ($targetIdStr === null || $resource === null || $lastTick === null) {
            $ship->stopSalvage();

            return false;
        }

        $field = $this->poiRepository->find(new PoiId($targetIdStr));
        if (!$field instanceof AsteroidField) {
            // Field weg (gelöscht / falscher Type) — Salvage stoppen.
            $ship->stopSalvage();

            return false;
        }

        $deltaSeconds = max(0, $now->getTimestamp() - $lastTick->getTimestamp());
        if ($deltaSeconds === 0) {
            return false;
        }

        $deltaMinutes = $deltaSeconds / 60.0;
        $rate = $ship->getType()->getSalvageRatePerMinute();
        $extractable = (int) floor($deltaMinutes * $rate);

        if ($extractable <= 0) {
            // Noch kein voller Unit-Tick erreicht — überspring, kein lastTick-Update.
            return false;
        }

        $available = $field->getAmount($resource);
        $cargoFree = $ship->getCargoFreeUnits();
        $taken = min($extractable, $available, $cargoFree);

        if ($taken > 0) {
            $field->extract($resource, $taken);
            $ship->loadResourceCargo($resource, $taken);
        }

        $ship->updateSalvageTick($now);

        // Stop-Conditions
        $newFieldAmount = $field->getAmount($resource);
        $newCargoFree = $ship->getCargoFreeUnits();
        if ($newFieldAmount === 0 || $newCargoFree === 0) {
            $ship->stopSalvage();
        }

        // Field-Cleanup
        if ($field->isEmpty()) {
            $this->em->remove($field);
        }

        return true;
    }
}
