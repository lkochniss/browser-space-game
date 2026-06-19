<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-015: ships.cargo_resources/cargo_pop_count/cargo_capacity (Transport-Cargo)';
    }

    public function up(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->addColumn('cargo_resources', 'json', ['notnull' => true, 'default' => '[]']);
        $ships->addColumn('cargo_pop_count', 'integer', ['notnull' => true, 'default' => 0]);
        $ships->addColumn('cargo_capacity', 'integer', ['notnull' => true, 'default' => 0]);
    }

    public function down(Schema $schema): void
    {
        $ships = $schema->getTable('ships');
        $ships->dropColumn('cargo_capacity');
        $ships->dropColumn('cargo_pop_count');
        $ships->dropColumn('cargo_resources');
    }
}
