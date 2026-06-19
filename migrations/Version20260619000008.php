<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-019: pois table (POI-Foundation, Single-Table-Inheritance)';
    }

    public function up(Schema $schema): void
    {
        $pois = $schema->createTable('pois');
        $pois->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $pois->addColumn('type', 'string', ['length' => 32]);
        $pois->addColumn('solar_system_id', 'string', ['length' => 36, 'fixed' => true]);
        $pois->addColumn('name', 'string', ['length' => 64, 'notnull' => false]);
        $pois->setPrimaryKey(['id']);
        $pois->addIndex(['solar_system_id'], 'idx_pois_solar_system');
        $pois->addIndex(['type'], 'idx_pois_type');
        $pois->addForeignKeyConstraint('solar_systems', ['solar_system_id'], ['id'], [], 'fk_pois_solar_system');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('pois');
    }
}
