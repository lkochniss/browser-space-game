<?php

declare(strict_types=1);

namespace App\Tests\POI\Persistence;

use App\POI\Model\AsteroidField;
use App\POI\Repository\PoiRepository;
use App\POI\ValueObject\PoiId;
use App\Resource\ValueObject\ResourceType;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use App\Tests\Integration\IntegrationTestCase;

final class AsteroidFieldPersistenceTest extends IntegrationTestCase
{
    public function test_asteroid_field_persists_with_contents_and_loads_as_subtype(): void
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $field = new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Belt-Alpha',
            contents: [
                ResourceType::IRON_ORE->value => 1500,
                ResourceType::COAL->value => 700,
            ],
        );

        $this->em->persist($system);
        $this->em->persist($field);
        $this->em->flush();

        $fieldId = $field->getId();
        $this->em->clear();

        $reloaded = self::getContainer()->get(PoiRepository::class)->find($fieldId);
        self::assertInstanceOf(AsteroidField::class, $reloaded);
        self::assertSame(1500, $reloaded->getAmount(ResourceType::IRON_ORE));
        self::assertSame(700, $reloaded->getAmount(ResourceType::COAL));
        self::assertSame(2200, $reloaded->getTotalAmount());
    }

    public function test_extract_persists_after_flush(): void
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');
        $field = new AsteroidField(
            id: PoiId::generate(),
            solarSystem: $system,
            contents: [ResourceType::IRON_ORE->value => 1000],
        );

        $this->em->persist($system);
        $this->em->persist($field);
        $this->em->flush();

        $field->extract(ResourceType::IRON_ORE, 400);
        $this->em->flush();

        $fieldId = $field->getId();
        $this->em->clear();

        $reloaded = self::getContainer()->get(PoiRepository::class)->find($fieldId);
        self::assertSame(600, $reloaded->getAmount(ResourceType::IRON_ORE));
    }
}
