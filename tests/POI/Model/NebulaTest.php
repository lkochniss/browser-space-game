<?php

declare(strict_types=1);

namespace App\Tests\POI\Model;

use App\POI\Model\Nebula;
use App\POI\ValueObject\PoiId;
use App\SolarSystem\Model\SolarSystem;
use App\SolarSystem\ValueObject\SolarSystemId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NebulaTest extends TestCase
{
    public function test_default_concealment_level(): void
    {
        $nebula = $this->makeNebula(5);
        self::assertSame(5, $nebula->getConcealmentLevel());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function validLevelProvider(): array
    {
        return [
            'min' => [1],
            'mid' => [5],
            'max' => [10],
        ];
    }

    #[DataProvider('validLevelProvider')]
    public function test_valid_concealment_level(int $level): void
    {
        $nebula = $this->makeNebula($level);
        self::assertSame($level, $nebula->getConcealmentLevel());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidLevelProvider(): array
    {
        return [
            'zero'        => [0],
            'negative'    => [-1],
            'too_high'    => [11],
            'way_too_high' => [100],
        ];
    }

    #[DataProvider('invalidLevelProvider')]
    public function test_invalid_concealment_level_throws(int $level): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeNebula($level);
    }

    public function test_set_concealment_after_construct(): void
    {
        $nebula = $this->makeNebula(5);
        $nebula->setConcealmentLevel(8);
        self::assertSame(8, $nebula->getConcealmentLevel());
    }

    private function makeNebula(int $level): Nebula
    {
        $system = new SolarSystem(SolarSystemId::generate(), 'Sol-Test');

        return new Nebula(
            id: PoiId::generate(),
            solarSystem: $system,
            name: 'Test-Nebula',
            concealmentLevel: $level,
        );
    }
}
