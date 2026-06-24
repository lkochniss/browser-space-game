<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-103 Battle-Resolution-Engine Foundation:
 *  - `battles` Table für Battle-Entity (id, attacker, fleets/planet, location,
 *    status, rounds, timestamps)
 *  - `ships.battle_current_hp` Column (nullable; persistiert HP zwischen Rounds)
 */
final class Version20260624000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-103: battles table + ships.battle_current_hp';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('battles');
        $table->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('attacker_player_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $table->addColumn('attacker_fleet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $table->addColumn('defender_fleet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $table->addColumn('defender_planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $table->addColumn('location_system_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 32]);
        $table->addColumn('rounds', 'integer', ['default' => 0]);
        $table->addColumn('started_at', 'datetime_immutable');
        $table->addColumn('ended_at', 'datetime_immutable', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['attacker_player_id']);
        $table->addIndex(['status']);
        $table->addForeignKeyConstraint('players', ['attacker_player_id'], ['id'], ['onDelete' => 'SET NULL']);
        $table->addForeignKeyConstraint('fleets', ['attacker_fleet_id'], ['id'], ['onDelete' => 'SET NULL']);
        $table->addForeignKeyConstraint('fleets', ['defender_fleet_id'], ['id'], ['onDelete' => 'SET NULL']);
        $table->addForeignKeyConstraint('planets', ['defender_planet_id'], ['id'], ['onDelete' => 'SET NULL']);
        $table->addForeignKeyConstraint('solar_systems', ['location_system_id'], ['id'], ['onDelete' => 'SET NULL']);

        $shipsTable = $schema->getTable('ships');
        $shipsTable->addColumn('battle_current_hp', 'integer', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('battles');
        $schema->getTable('ships')->dropColumn('battle_current_hp');
    }
}
