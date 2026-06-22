<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-150: Player.bubble_status (Anti-Crush-Tutorial-Phase). Bestehende Player
 * defaulten auf BUBBLE — sie können den Status durch Colonize ihres 2.
 * Planeten verlassen (oder bleiben in BUBBLE wenn sie noch nicht so weit
 * sind; falls bereits >= 2 Planeten existieren, läuft ein einmaliger Backfill).
 */
final class Version20260622000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-150: players.bubble_status (PlayerBubbleStatus, default bubble) + Backfill';
    }

    public function up(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->addColumn('bubble_status', 'string', [
            'length' => 16,
            'notnull' => true,
            'default' => 'bubble',
        ]);
    }

    public function postUp(Schema $schema): void
    {
        // Bestehende Player mit >= 2 Planeten haben die Tutorial-Phase bereits
        // hinter sich — direkt auf EXITED setzen.
        $this->connection->executeStatement(<<<'SQL'
            UPDATE players SET bubble_status = 'exited'
            WHERE id IN (
                SELECT player_id FROM planets
                WHERE player_id IS NOT NULL
                GROUP BY player_id
                HAVING COUNT(*) >= 2
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->dropColumn('bubble_status');
    }
}
