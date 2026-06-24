<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-104a Crew-Foundation:
 * - `crew` table für Captain-Entity (T-104c erweitert um Engineer/Diplomat)
 * - Crew-Lifecycle: TRAINING → IDLE → ASSIGNED → DEAD
 */
final class Version20260623000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-104a: crew table (Captain-Foundation)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('crew');
        $table->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('owner_id', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('type', 'string', ['length' => 16]);
        $table->addColumn('status', 'string', ['length' => 16]);
        $table->addColumn('level', 'integer', ['default' => 1]);
        $table->addColumn('xp', 'integer', ['default' => 0]);
        $table->addColumn('assigned_ship_id', 'string', [
            'length' => 36, 'fixed' => true, 'notnull' => false,
        ]);
        $table->addColumn('training_finished_at', 'datetime_immutable', ['notnull' => false]);
        $table->addColumn('last_boost_at', 'datetime_immutable', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'idx_crew_owner');
        $table->addIndex(['status'], 'idx_crew_status');
        $table->addIndex(['assigned_ship_id'], 'idx_crew_assigned_ship');
        $table->addForeignKeyConstraint('players', ['owner_id'], ['id'], [], 'fk_crew_owner');
        $table->addForeignKeyConstraint('ships', ['assigned_ship_id'], ['id'], [
            'onDelete' => 'SET NULL',
        ], 'fk_crew_assigned_ship');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('crew');
    }
}
