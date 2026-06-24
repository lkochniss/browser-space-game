<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-102 Ship-Classes Foundation: `ships.ship_class` Column (nullable).
 *
 * NULL → non-combat (existing Spezial-Schiffe via ShipType).
 * Non-NULL → eine der 15 Combat-Klassen (FRIGATE_MK1 .. CARRIER_MK3).
 */
final class Version20260624000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-102: ships.ship_class column for Combat-Schiff-Klassen';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('ships');
        $table->addColumn('ship_class', 'string', ['length' => 32, 'notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('ships')->dropColumn('ship_class');
    }
}
