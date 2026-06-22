<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-026c: Ships bekommen ein `propulsion`-Feld (PropulsionType). Bestehende
 * Schiffe defaulten auf HYDROGEN (Foundation-Antrieb, keine Forschung nötig).
 */
final class Version20260622000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-026c: ships.propulsion (PropulsionType, default hydrogen)';
    }

    public function up(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->addColumn('propulsion', 'string', [
            'length' => 16,
            'notnull' => true,
            'default' => 'hydrogen',
        ]);
    }

    public function down(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->dropColumn('propulsion');
    }
}
