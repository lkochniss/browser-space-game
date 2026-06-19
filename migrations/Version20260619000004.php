<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-012: ships table (Raumschiff-Foundation + Life-Support)';
    }

    public function up(Schema $schema): void
    {
        $ships = $schema->createTable('ships');
        $ships->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $ships->addColumn('type', 'string', ['length' => 32]);
        $ships->addColumn('planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $ships->addColumn('population_assigned', 'integer');
        $ships->addColumn('supply_water', 'integer');
        $ships->addColumn('supply_food', 'integer');
        $ships->addColumn('supply_oxygen', 'integer');
        $ships->addColumn('supply_capacity', 'integer');
        $ships->addColumn('finished_at', 'datetime_immutable', ['notnull' => false]);
        $ships->setPrimaryKey(['id']);
        $ships->addIndex(['planet_id'], 'idx_ships_planet');
        $ships->addForeignKeyConstraint('planets', ['planet_id'], ['id'], [], 'fk_ships_planet');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('ships');
    }
}
