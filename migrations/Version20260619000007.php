<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-017: fleets table + ships.fleet_id (Flotte + Movement)';
    }

    public function up(Schema $schema): void
    {
        $fleets = $schema->createTable('fleets');
        $fleets->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $fleets->addColumn('player_id', 'string', ['length' => 36, 'fixed' => true]);
        $fleets->addColumn('status', 'string', ['length' => 32]);
        $fleets->addColumn('origin_planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $fleets->addColumn('target_planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $fleets->addColumn('departed_at', 'datetime_immutable', ['notnull' => false]);
        $fleets->addColumn('arrived_at', 'datetime_immutable', ['notnull' => false]);
        $fleets->setPrimaryKey(['id']);
        $fleets->addIndex(['player_id'], 'idx_fleets_player');
        $fleets->addIndex(['status'], 'idx_fleets_status');
        $fleets->addForeignKeyConstraint('players', ['player_id'], ['id'], [], 'fk_fleets_player');
        $fleets->addForeignKeyConstraint('planets', ['origin_planet_id'], ['id'], [], 'fk_fleets_origin');
        $fleets->addForeignKeyConstraint('planets', ['target_planet_id'], ['id'], [], 'fk_fleets_target');

        $ships = $schema->getTable('ships');
        $ships->addColumn('fleet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $ships->addIndex(['fleet_id'], 'idx_ships_fleet');
        $ships->addForeignKeyConstraint('fleets', ['fleet_id'], ['id'], [], 'fk_ships_fleet');
    }

    public function down(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->removeForeignKey('fk_ships_fleet');
        $ships->dropIndex('idx_ships_fleet');
        $ships->dropColumn('fleet_id');

        $schema->dropTable('fleets');
    }
}
