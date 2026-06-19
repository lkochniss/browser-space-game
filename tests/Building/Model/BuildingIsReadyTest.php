<?php

declare(strict_types=1);

namespace App\Tests\Building\Model;

use App\Building\Model\Building;
use App\Building\ValueObject\BuildingId;
use App\Building\ValueObject\BuildingType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildingIsReadyTest extends TestCase
{
    public function test_finished_at_null_is_ready_with_or_without_clock(): void
    {
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);

        self::assertTrue($b->isReady(null));
        self::assertTrue($b->isReady(new DateTimeImmutable()));
    }

    public function test_finished_at_in_future_is_not_ready(): void
    {
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $b->setFinishedAt((new DateTimeImmutable())->modify('+10 minutes'));

        self::assertFalse($b->isReady(new DateTimeImmutable()));
    }

    public function test_finished_at_in_past_is_ready(): void
    {
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $b->setFinishedAt((new DateTimeImmutable())->modify('-10 minutes'));

        self::assertTrue($b->isReady(new DateTimeImmutable()));
    }

    public function test_finished_at_set_but_no_clock_is_conservative_not_ready(): void
    {
        // Pattern: addBuilding without explicit clock during T-062 transition.
        // Building has finishedAt but recalc has no $now → don't count.
        $b = new Building(BuildingId::generate(), BuildingType::HUB, 1);
        $b->setFinishedAt(new DateTimeImmutable());

        self::assertFalse($b->isReady(null));
    }

    public function test_finished_at_exactly_equals_now_is_ready(): void
    {
        $now = new DateTimeImmutable();
        $b = new Building(BuildingId::generate(), BuildingType::IRON_MINE, 1);
        $b->setFinishedAt($now);

        self::assertTrue($b->isReady($now));
    }
}
