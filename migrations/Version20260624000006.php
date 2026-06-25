<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * T-178 Ship-Cargo-Universal: alle Schiffe haben Volume-Cargo.
 *
 * Schema-Cut:
 *  - `ships.cargo_capacity` (Units-basiert, T-015) → drop
 *  - `ships.cargo_volume_capacity` (m³, T-178) → add
 *  - `cargo_resources` JSON + `cargo_pop_count` bleiben (Embeddable
 *    `ShipCargo` nutzt die gleichen Columns wie der alte `CargoManifest`)
 *
 * Datenkonsequenz: Existing-Ships verlieren ihre Volume-Cap (default 0).
 * Demo/Test-State wird via Reset neu gebaut — keine Backfill-Logik nötig.
 */
final class Version20260624000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'T-178: ships.cargo_volume_capacity (m³) ersetzt cargo_capacity (units)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('ships');
        if ($table->hasColumn('cargo_capacity')) {
            $table->dropColumn('cargo_capacity');
        }
        if (!$table->hasColumn('cargo_volume_capacity')) {
            $table->addColumn('cargo_volume_capacity', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('ships');
        if ($table->hasColumn('cargo_volume_capacity')) {
            $table->dropColumn('cargo_volume_capacity');
        }
        if (!$table->hasColumn('cargo_capacity')) {
            $table->addColumn('cargo_capacity', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
        }
    }
}
