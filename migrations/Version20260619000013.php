<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-016: ships.salvage_* (Bergungsschiff Echtzeit-Salvage-State)';
    }

    public function up(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->addColumn('salvage_target_poi_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $ships->addColumn('salvage_resource_type', 'string', ['length' => 32, 'notnull' => false]);
        $ships->addColumn('salvage_last_tick_at', 'datetime_immutable', ['notnull' => false]);
        $ships->addIndex(['salvage_target_poi_id'], 'idx_ships_salvage_target');
    }

    public function down(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->dropIndex('idx_ships_salvage_target');
        $ships->dropColumn('salvage_last_tick_at');
        $ships->dropColumn('salvage_resource_type');
        $ships->dropColumn('salvage_target_poi_id');
    }
}
