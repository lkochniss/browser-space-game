<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-022: pois.nebula_concealment (Nebula subtype data)';
    }

    public function up(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->addColumn('nebula_concealment', 'integer', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->dropColumn('nebula_concealment');
    }
}
