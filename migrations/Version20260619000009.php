<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-020: pois.asteroid_contents (AsteroidField subtype data)';
    }

    public function up(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->addColumn('asteroid_contents', 'json', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->dropColumn('asteroid_contents');
    }
}
