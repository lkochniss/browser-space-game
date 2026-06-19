<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-073: factions + player_faction_reputation tables (NPC-Faction-Foundation)';
    }

    public function up(Schema $schema): void
    {
        $factions = $schema->createTable('factions');
        $factions->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $factions->addColumn('slug', 'string', ['length' => 64]);
        $factions->addColumn('name', 'string', ['length' => 128]);
        $factions->addColumn('type', 'string', ['length' => 32]);
        $factions->addColumn('is_always_hostile', 'boolean');
        $factions->addColumn('default_reputation', 'integer');
        $factions->addColumn('description', 'text');
        $factions->setPrimaryKey(['id']);
        $factions->addUniqueIndex(['slug'], 'uniq_factions_slug');

        $reputation = $schema->createTable('player_faction_reputation');
        $reputation->addColumn('player_id', 'string', ['length' => 36, 'fixed' => true]);
        $reputation->addColumn('faction_id', 'string', ['length' => 36, 'fixed' => true]);
        $reputation->addColumn('value', 'integer');
        $reputation->setPrimaryKey(['player_id', 'faction_id']);
        $reputation->addIndex(['player_id'], 'idx_pfr_player');
        $reputation->addIndex(['faction_id'], 'idx_pfr_faction');
        $reputation->addForeignKeyConstraint('players', ['player_id'], ['id'], [], 'fk_pfr_player');
        $reputation->addForeignKeyConstraint('factions', ['faction_id'], ['id'], [], 'fk_pfr_faction');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('player_faction_reputation');
        $schema->dropTable('factions');
    }
}
