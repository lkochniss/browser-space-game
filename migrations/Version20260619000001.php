<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-007: solar_systems table + planets.solar_system_id FK';
    }

    public function up(Schema $schema): void
    {
        $systems = $schema->createTable('solar_systems');
        $systems->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $systems->addColumn('name', 'string', ['length' => 64]);
        $systems->setPrimaryKey(['id']);

        $planets = $schema->getTable('planets');
        $planets->addColumn('solar_system_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $planets->addIndex(['solar_system_id'], 'idx_planets_solar_system');
        $planets->addForeignKeyConstraint('solar_systems', ['solar_system_id'], ['id'], [], 'fk_planets_solar_system');
    }

    public function down(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->removeForeignKey('fk_planets_solar_system');
        $planets->dropIndex('idx_planets_solar_system');
        $planets->dropColumn('solar_system_id');

        $schema->dropTable('solar_systems');
    }
}
