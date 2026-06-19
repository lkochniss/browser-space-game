<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-085: pois.wormhole_twin_id + pois.wormhole_tech_slug (Wormhole subtype data)';
    }

    public function up(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->addColumn('wormhole_twin_id', 'string', ['length' => 36, 'fixed' => true, 'notnull' => false]);
        $pois->addColumn('wormhole_tech_slug', 'string', ['length' => 64, 'notnull' => false]);
        $pois->addIndex(['wormhole_twin_id'], 'idx_pois_wormhole_twin');
        $pois->addForeignKeyConstraint(
            'pois',
            ['wormhole_twin_id'],
            ['id'],
            [],
            'fk_pois_wormhole_twin',
        );
    }

    public function down(Schema $schema): void
    {
        $pois = $schema->getTable('pois');
        $pois->removeForeignKey('fk_pois_wormhole_twin');
        $pois->dropIndex('idx_pois_wormhole_twin');
        $pois->dropColumn('wormhole_tech_slug');
        $pois->dropColumn('wormhole_twin_id');
    }
}
