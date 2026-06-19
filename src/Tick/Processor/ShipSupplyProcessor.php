<?php

declare(strict_types=1);

namespace App\Tick\Processor;

use App\POI\Model\DebrisField;
use App\POI\ValueObject\PoiId;
use App\Planet\Model\Planet;
use App\Resource\Model\Resource;
use App\Resource\ValueObject\ResourceType;
use App\Ship\Model\Ship;
use App\Ship\Repository\ShipRepository;
use App\Tick\Interface\TickProcessorInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-012 Schiff-Life-Support pro Tick.
 *
 * Pro fertigem Schiff (isReady):
 * - dockedAt-Planet: zieht 1 W + 1 F + 1 O aus Planet-Storage. Falls Planet-Storage leer:
 *   drain Schiff-Storage; falls auch leer → Schiff stirbt.
 * - undocked (planet=null): drain Schiff-Storage; falls leer → Schiff stirbt.
 *
 * Schiff-Death (T-012-Decision):
 * - Pop-Slots werden komplett verloren (release + kill auf Planet, wo das Schiff zuletzt war).
 * - T-021: kleines DebrisField (1-3 DEBRIS_LOW) spawnt im Heim-System.
 *
 * Hinweis: Undocked-Schiffe haben in T-012 noch keine Quelle (kein Movement). Der Code
 * behandelt den Fall trotzdem konsistent — T-017 wird Movement aktivieren.
 */
readonly class ShipSupplyProcessor implements TickProcessorInterface
{
    public const CONSUMPTION_PER_TICK = 1;

    public function __construct(
        private ShipRepository $shipRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function process(Planet $planet, ?DateTimeImmutable $now = null): void
    {
        foreach ($this->shipRepository->findByPlanet($planet) as $ship) {
            if (!$ship->isReady($now)) {
                continue;
            }
            $this->supplyDockedShip($planet, $ship);
        }
    }

    private function supplyDockedShip(Planet $planet, Ship $ship): void
    {
        $waterRes = $planet->ensureResource(ResourceType::WATER);
        $foodRes = $planet->ensureResource(ResourceType::FOOD);
        $oxygenRes = $planet->ensureResource(ResourceType::OXYGEN);

        $waterFromPlanet = $this->drainPlanet($waterRes);
        $foodFromPlanet = $this->drainPlanet($foodRes);
        $oxygenFromPlanet = $this->drainPlanet($oxygenRes);

        $waterShortage = self::CONSUMPTION_PER_TICK - $waterFromPlanet;
        $foodShortage = self::CONSUMPTION_PER_TICK - $foodFromPlanet;
        $oxygenShortage = self::CONSUMPTION_PER_TICK - $oxygenFromPlanet;

        if ($waterShortage === 0 && $foodShortage === 0 && $oxygenShortage === 0) {
            return;
        }

        $newWater = $ship->getSupplyWater() - $waterShortage;
        $newFood = $ship->getSupplyFood() - $foodShortage;
        $newOxygen = $ship->getSupplyOxygen() - $oxygenShortage;

        if ($newWater < 0 || $newFood < 0 || $newOxygen < 0) {
            $this->killShip($planet, $ship);

            return;
        }

        $ship->setSupplies($newWater, $newFood, $newOxygen);
    }

    private function drainPlanet(Resource $resource): int
    {
        $taken = min(self::CONSUMPTION_PER_TICK, $resource->getAmount());
        if ($taken > 0) {
            $resource->setAmount($resource->getAmount() - $taken);
        }

        return $taken;
    }

    private function killShip(Planet $planet, Ship $ship): void
    {
        $pop = $planet->getPopulation();
        $assigned = $ship->getPopulationAssigned();

        // Crew geht verloren: Pop-Slot wird zuerst released (assigned→free),
        // dann free gekillt — Netto: assigned -= n, total -= n.
        $pop->release($assigned);
        $pop->kill($assigned);

        // T-021: Mini-DebrisField im Heim-System spawnen, wenn System bekannt.
        $sys = $planet->getSolarSystem();
        if ($sys !== null) {
            $debris = new DebrisField(
                id: PoiId::generate(),
                solarSystem: $sys,
                name: sprintf('Schiff-Wrack (%s)', $ship->getType()->value),
                contents: [ResourceType::DEBRIS_LOW->value => 2],
            );
            $sys->addPoi($debris);
            $this->em->persist($debris);
        }

        $this->em->remove($ship);
    }
}
