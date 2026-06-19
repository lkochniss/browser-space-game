<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Faction\Service\FactionSeedService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $tool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        self::getContainer()->get(FactionSeedService::class)->seed();
    }

    protected function tearDown(): void
    {
        $this->em->clear();
        $this->em->close();
        parent::tearDown();
    }
}
