<?php

declare(strict_types=1);

namespace App\Tests\Planet\Model;

use App\Planet\Model\Population;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PopulationTest extends TestCase
{
    public function test_empty_initializes_with_zero_total_and_default_cap(): void
    {
        $pop = Population::empty();

        self::assertSame(0, $pop->getTotal());
        self::assertSame(0, $pop->getAssigned());
        self::assertSame(100, $pop->getCap());
        self::assertSame(0, $pop->getFree());
    }

    public function test_grow_increases_total_capped_at_cap(): void
    {
        $pop = Population::empty(cap: 100);
        $pop->grow(50);
        self::assertSame(50, $pop->getTotal());
        self::assertSame(50, $pop->getFree());

        $pop->grow(80); // would overflow → clamped at cap
        self::assertSame(100, $pop->getTotal());
    }

    public function test_assign_reduces_free_and_increases_assigned(): void
    {
        $pop = new Population(total: 50, assigned: 0, cap: 100);
        $pop->assign(20);

        self::assertSame(20, $pop->getAssigned());
        self::assertSame(30, $pop->getFree());
        self::assertSame(50, $pop->getTotal());
    }

    public function test_assign_more_than_free_throws(): void
    {
        $pop = new Population(total: 10, assigned: 0, cap: 100);

        $this->expectException(InvalidArgumentException::class);
        $pop->assign(11);
    }

    public function test_release_returns_assigned_to_free(): void
    {
        $pop = new Population(total: 50, assigned: 30, cap: 100);
        $pop->release(10);

        self::assertSame(20, $pop->getAssigned());
        self::assertSame(30, $pop->getFree());
    }

    public function test_release_more_than_assigned_throws(): void
    {
        $pop = new Population(total: 10, assigned: 5, cap: 100);

        $this->expectException(InvalidArgumentException::class);
        $pop->release(6);
    }

    public function test_kill_takes_from_free_first_then_assigned(): void
    {
        $pop = new Population(total: 50, assigned: 30, cap: 100);
        // free = 20; kill 10 → all from free
        $pop->kill(10);
        self::assertSame(40, $pop->getTotal());
        self::assertSame(30, $pop->getAssigned());
        self::assertSame(10, $pop->getFree());

        // free = 10; kill 25 → 10 from free, 15 from assigned
        $pop->kill(25);
        self::assertSame(15, $pop->getTotal());
        self::assertSame(15, $pop->getAssigned());
        self::assertSame(0, $pop->getFree());
    }

    public function test_kill_more_than_total_clamps_to_zero(): void
    {
        $pop = new Population(total: 10, assigned: 5, cap: 100);
        $pop->kill(999);

        self::assertSame(0, $pop->getTotal());
        self::assertSame(0, $pop->getAssigned());
    }

    public function test_setCap_lowers_total_if_above(): void
    {
        $pop = new Population(total: 80, assigned: 60, cap: 100);
        $pop->setCap(50);

        self::assertSame(50, $pop->getCap());
        self::assertSame(50, $pop->getTotal());
        self::assertSame(50, $pop->getAssigned());
    }

    public function test_setCap_raises_does_not_grow_total(): void
    {
        $pop = new Population(total: 50, assigned: 20, cap: 100);
        $pop->setCap(200);

        self::assertSame(200, $pop->getCap());
        self::assertSame(50, $pop->getTotal());
        self::assertSame(20, $pop->getAssigned());
    }

    public function test_negative_amounts_throw(): void
    {
        $pop = new Population(total: 10, assigned: 0, cap: 100);

        $this->expectException(InvalidArgumentException::class);
        $pop->assign(-1);
    }

    public function test_constructor_rejects_assigned_greater_than_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Population(total: 10, assigned: 11, cap: 100);
    }

    public function test_constructor_rejects_total_greater_than_cap(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Population(total: 101, assigned: 0, cap: 100);
    }
}
