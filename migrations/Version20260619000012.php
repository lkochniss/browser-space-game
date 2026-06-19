<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-023: pois.station_* (SpaceStation subtype data: owner, status, population, storage)';
    }

    public function up(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->addColumn('station_owner_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $pois->addColumn('station_status', 'string', ['length' => 16, 'notnull' => false]);
        $pois->addColumn('station_population', 'integer', ['notnull' => false]);
        $pois->addColumn('station_storage_capacity', 'integer', ['notnull' => false]);
        $pois->addColumn('station_cargo_resources', 'json', ['notnull' => false]);
        $pois->addColumn('station_cargo_pop_count', 'integer', ['notnull' => false]);
        $pois->addIndex(['station_owner_id'], 'idx_pois_station_owner');
        $pois->addForeignKeyConstraint('players', ['station_owner_id'], ['id'], [], 'fk_pois_station_owner');
    }

    public function down(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->removeForeignKey('fk_pois_station_owner');
        $pois->dropIndex('idx_pois_station_owner');
        $pois->dropColumn('station_cargo_pop_count');
        $pois->dropColumn('station_cargo_resources');
        $pois->dropColumn('station_storage_capacity');
        $pois->dropColumn('station_population');
        $pois->dropColumn('station_status');
        $pois->dropColumn('station_owner_id');
    }
}
