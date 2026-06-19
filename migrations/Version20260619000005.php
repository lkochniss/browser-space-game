<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-013: probes table (Sonden-Foundation)';
    }

    public function up(Schema $schema): void
    {
        $probes = $schema->createTable('probes');
        $probes->addColumn('id', 'string', ['length' => 36, 'fixed' => true]);
        $probes->addColumn('type', 'string', ['length' => 32]);
        $probes->addColumn('planet_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $probes->addColumn('finished_at', 'datetime_immutable', ['notnull' => false]);
        $probes->setPrimaryKey(['id']);
        $probes->addIndex(['planet_id'], 'idx_probes_planet');
        $probes->addForeignKeyConstraint('planets', ['planet_id'], ['id'], [], 'fk_probes_planet');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('probes');
    }
}
