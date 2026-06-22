<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-122: Player.background (Imperial-Flavor-Background, nullable). Bestehende
 * Player haben NULL — die Wahl wird via Onboarding (T-046) bzw. Demo-CLI
 * gemacht; ohne Wahl gibt es keine Background-Multiplier (T-122b).
 */
final class Version20260622000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-122: players.background (PlayerBackground, nullable)';
    }

    public function up(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->addColumn('background', 'string', [
            'length' => 32,
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $players = $schema->getTable('players');
        $players->dropColumn('background');
    }
}
