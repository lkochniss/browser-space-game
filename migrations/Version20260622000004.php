<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-081: planets.is_home_planet Foundation-Flag. Backfill markiert pro Player
 * den ersten Planeten (alphabetisch nach ID) als Heimat. In Fresh-DBs ist das
 * der via ClaimStartPlanetCommandService erstellte Start-Planet.
 *
 * Per-Player-Uniqueness wird heute auf Application-Ebene durchgesetzt
 * (`ClaimStartPlanetCommandService` markiert genau einen Planeten); keine
 * harte DB-Constraint, da die spätere "Heimat verlegen"-Mechanik (T-081b)
 * eventuell andere Semantik braucht.
 */
final class Version20260622000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-081: planets.is_home_planet (Heimat-Schutz-Foundation) + Backfill';
    }

    public function up(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->addColumn('is_home_planet', 'boolean', [
            'notnull' => true,
            'default' => false,
        ]);
    }

    public function postUp(Schema $schema): void
    {
        // Backfill: pro Player den ersten Planeten (ORDER BY id ASC) als Heimat
        // markieren. Idempotent (kein Update wenn schon ein Home existiert).
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT player_id, MIN(id) AS first_planet_id
            FROM planets
            WHERE player_id IS NOT NULL
            GROUP BY player_id
        SQL);

        foreach ($rows as $row) {
            $playerId = $row['player_id'];
            $firstPlanetId = $row['first_planet_id'];

            $existingHome = $this->connection->fetchOne(
                'SELECT id FROM planets WHERE player_id = ? AND is_home_planet = 1 LIMIT 1',
                [$playerId],
            );
            if ($existingHome !== false) {
                continue;
            }

            $this->connection->executeStatement(
                'UPDATE planets SET is_home_planet = 1 WHERE id = ?',
                [$firstPlanetId],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->dropColumn('is_home_planet');
    }
}
