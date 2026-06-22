<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-025c: ActiveResearch trackt jetzt Primary-Lab-Planet + Booster-Lab-Planet-
 * IDs (Multi-Lab Opt-In). Bestehende Rows bekommen NULL primary + leeres
 * Booster-Array (entspricht Single-Lab-Verhalten).
 */
final class Version20260622000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-025c: active_research.primary_planet_id + booster_planet_ids';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('active_research');
        $table->addColumn('primary_planet_id', 'string', [
            'length' => 36,
            'fixed' => true,
            'notnull' => false,
        ]);
        $table->addColumn('booster_planet_ids', 'json', [
            'notnull' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('active_research');
        $table->dropColumn('booster_planet_ids');
        $table->dropColumn('primary_planet_id');
    }
}
