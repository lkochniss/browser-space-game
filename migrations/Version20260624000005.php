<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-110 Trade-Routes:
 *  - `trade_routes` Table für TradeRoute-Entity
 *    (id, owner, source/target Planet, bound Ship, outbound/return Resources,
 *    status, leg, lastTripAt, tripCounter)
 */
final class Version20260624000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-110: trade_routes table (Auto-Transport-Routes)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('trade_routes');
        $table->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('owner_id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('source_planet_id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('target_planet_id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('bound_ship_id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('outbound_resource', 'string', ['length' => 32]);
        $table->addColumn('outbound_qty', 'integer');
        $table->addColumn('return_resource', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('return_qty', 'integer', ['notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 16]);
        $table->addColumn('current_leg', 'string', ['length' => 32]);
        $table->addColumn('last_trip_at', 'datetime_immutable', ['notnull' => false]);
        $table->addColumn('trip_counter', 'integer', ['default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id']);
        $table->addIndex(['status']);
        $table->addIndex(['bound_ship_id']);
        $table->addForeignKeyConstraint('players', ['owner_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('planets', ['source_planet_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('planets', ['target_planet_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('ships', ['bound_ship_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('trade_routes');
    }
}
