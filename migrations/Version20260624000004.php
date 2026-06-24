<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-104b Captain-Skill-Trees: `crew.skill_allocation` JSON-Column.
 *
 * Map<TreeName.value, int> mit 4 Keys (beam_master, missile_specialist,
 * shield_tactician, fleet_commander). Default `{}` — existierende Captains
 * starten ohne Allocation.
 */
final class Version20260624000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-104b: crew.skill_allocation JSON-Column';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('crew');
        $table->addColumn('skill_allocation', 'json', ['default' => '{}']);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('crew')->dropColumn('skill_allocation');
    }
}
