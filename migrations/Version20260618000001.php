<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: players, planets, buildings, resources, resource_deposits';
    }

    public function up(Schema $schema): void
    {
        $players = $schema->createTable('players');
        $players->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $players->setPrimaryKey(['id']);

        $planets = $schema->createTable('planets');
        $planets->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $planets->addColumn('player_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $planets->setPrimaryKey(['id']);
        $planets->addIndex(['player_id'], 'idx_planets_player');
        $planets->addForeignKeyConstraint('players', ['player_id'], ['id'], [], 'fk_planets_player');

        $buildings = $schema->createTable('buildings');
        $buildings->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $buildings->addColumn('planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $buildings->addColumn('type', 'string', ['length' => 32]);
        $buildings->addColumn('level', 'integer');
        $buildings->setPrimaryKey(['id']);
        $buildings->addIndex(['planet_id'], 'idx_buildings_planet');
        $buildings->addForeignKeyConstraint('planets', ['planet_id'], ['id'], [], 'fk_buildings_planet');

        $resources = $schema->createTable('resources');
        $resources->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $resources->addColumn('planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $resources->addColumn('type', 'string', ['length' => 32]);
        $resources->addColumn('amount', 'integer');
        $resources->setPrimaryKey(['id']);
        $resources->addIndex(['planet_id'], 'idx_resources_planet');
        $resources->addForeignKeyConstraint('planets', ['planet_id'], ['id'], [], 'fk_resources_planet');

        $deposits = $schema->createTable('resource_deposits');
        $deposits->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $deposits->addColumn('planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $deposits->addColumn('resource_type', 'string', ['length' => 32]);
        $deposits->addColumn('amount', 'integer');
        $deposits->setPrimaryKey(['id']);
        $deposits->addIndex(['planet_id'], 'idx_resource_deposits_planet');
        $deposits->addForeignKeyConstraint('planets', ['planet_id'], ['id'], [], 'fk_resource_deposits_planet');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('resource_deposits');
        $schema->dropTable('resources');
        $schema->dropTable('buildings');
        $schema->dropTable('planets');
        $schema->dropTable('players');
    }
}
