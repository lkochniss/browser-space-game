<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add finished_at to buildings (real-time construction stub for T-062)';
    }

    public function up(Schema $schema): void
    {
        $buildings = $schema->getTable('buildings');
        $buildings->addColumn('finished_at', 'datetime_immutable', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $buildings = $schema->getTable('buildings');
        $buildings->dropColumn('finished_at');
    }
}
