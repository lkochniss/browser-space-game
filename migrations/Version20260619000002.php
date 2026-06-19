<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-008: planets.type + planets.size columns (PlanetType + PlanetSize enums)';
    }

    public function up(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->addColumn('type', 'string', [
            'length' => 32,
            'default' => 'terran',
            'notnull' => true,
        ]);
        $planets->addColumn('size', 'string', [
            'length' => 32,
            'default' => 'medium',
            'notnull' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->dropColumn('size');
        $planets->dropColumn('type');
    }
}
