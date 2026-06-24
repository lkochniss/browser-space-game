<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-068 Defense-Buildings: HP-State + Repair-Cooldown auf Buildings.
 *
 * Existing Buildings werden mit `current_hp = 0` initialisiert. Defense-
 * Buildings haben dann sofort 0 HP — Acceptable, weil Defense-Buildings
 * heute noch nicht existieren (T-068 führt die Types erst ein).
 */
final class Version20260624000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-068: buildings.current_hp + last_repair_at columns';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('buildings');
        $table->addColumn('current_hp', 'integer', ['default' => 0]);
        $table->addColumn('last_repair_at', 'datetime_immutable', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('buildings');
        $table->dropColumn('current_hp');
        $table->dropColumn('last_repair_at');
    }
}
