<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add population (total/assigned/cap) to planets table';
    }

    public function up(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->addColumn('population_total', 'integer', ['default' => 0, 'notnull' => true]);
        $planets->addColumn('population_assigned', 'integer', ['default' => 0, 'notnull' => true]);
        $planets->addColumn('population_cap', 'integer', ['default' => 100, 'notnull' => true]);
    }

    public function down(Schema $schema): void
    {
        $planets = $schema->getTable('planets');
        $planets->dropColumn('population_total');
        $planets->dropColumn('population_assigned');
        $planets->dropColumn('population_cap');
    }
}
