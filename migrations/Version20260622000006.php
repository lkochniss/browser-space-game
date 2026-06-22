<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-096: Player-Lifetime-Stats Foundation. 3 Counter direkt auf players-Tabelle
 * (Foundation pragmatisch — eigene PlayerStats-Entity wäre Overengineering für
 * 3 ints). Mining-Total + Battle-Counters folgen in T-096b.
 */
final class Version20260622000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-096: players.stats_buildings_built/planets_colonized/ships_built';
    }

    public function up(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->addColumn('stats_buildings_built', 'integer', [
            'notnull' => true,
            'default' => 0,
        ]);
        $players->addColumn('stats_planets_colonized', 'integer', [
            'notnull' => true,
            'default' => 0,
        ]);
        $players->addColumn('stats_ships_built', 'integer', [
            'notnull' => true,
            'default' => 0,
        ]);
    }

    public function down(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->dropColumn('stats_ships_built');
        $players->dropColumn('stats_planets_colonized');
        $players->dropColumn('stats_buildings_built');
    }
}
