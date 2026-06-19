<?php

declare(strict_types=1);

namespace App\Probe\Service;

use App\Common\Interface\ClockInterface;
use App\Planet\Model\Planet;
use App\Planet\Repository\PlanetRepository;
use App\Planet\ValueObject\PlanetId;
use App\Probe\Exception\InsufficientResourcesException;
use App\Probe\Exception\MissingProbeLabException;
use App\Probe\Exception\PlanetNotFoundException;
use App\Probe\Model\Probe;
use App\Probe\ValueObject\ProbeId;
use App\Probe\ValueObject\ProbeType;
use App\Resource\ValueObject\ResourceType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

/**
 * T-013 Foundation. Build-Only:
 * - Cost + Bauzeit pro ProbeType (siehe ProbeCostConfig)
 * - Voraussetzung: Planet hat fertige PROBE_LAB (T-013 Building) auf Level >= 1
 * - Wallclock-Build analog T-062
 * - Kein Pop-Cost (Sonden sind unbemannte Geräte, Doc-konform)
 *
 * Discovery-Effekt der Sonde wird in T-018 (Teleskop) / T-087 (Fog-of-War) gebaut.
 */
readonly class BuildProbeCommandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanetRepository $planetRepository,
        private ProbeCostConfig $costConfig,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlanetId $planetId, ProbeType $type): Probe
    {
        $planet = $this->planetRepository->find($planetId);
        if ($planet === null) {
            throw new PlanetNotFoundException($planetId);
        }

        $now = $this->clock->now();

        if (!$planet->hasProbeLab($now)) {
            throw new MissingProbeLabException($planetId);
        }

        $cost = $this->costConfig->getResourceCost($type);

        $this->checkResources($planet, $cost);
        $this->debitResources($planet, $cost);

        $probe = new Probe(
            id: ProbeId::generate(),
            type: $type,
        );
        $probe->setPlanet($planet);

        $duration = $this->costConfig->getDurationSeconds($type);
        $probe->setFinishedAt($now->add(new DateInterval(sprintf('PT%dS', $duration))));

        $this->em->persist($probe);
        $this->em->flush();

        return $probe;
    }

    /**
     * @param array<string,int> $cost
     */
    private function checkResources(Planet $planet, array $cost): void
    {
        foreach ($cost as $resourceTypeValue => $required) {
            $resourceType = ResourceType::from($resourceTypeValue);
            $available = $this->getResourceAmount($planet, $resourceType);
            if ($available < $required) {
                throw new InsufficientResourcesException($resourceType, $required, $available);
            }
        }
    }

    /**
     * @param array<string,int> $cost
     */
    private function debitResources(Planet $planet, array $cost): void
    {
        foreach ($cost as $resourceTypeValue => $amount) {
            $resourceType = ResourceType::from($resourceTypeValue);
            $resource = $planet->getResource($resourceType);
            $resource->setAmount($resource->getAmount() - $amount);
        }
    }

    private function getResourceAmount(Planet $planet, ResourceType $type): int
    {
        foreach ($planet->getResources() as $resource) {
            if ($resource->getType() === $type) {
                return $resource->getAmount();
            }
        }

        return 0;
    }
}
