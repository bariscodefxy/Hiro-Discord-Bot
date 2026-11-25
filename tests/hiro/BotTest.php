<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\Hiro;
use hiro\Version;
use hiro\interfaces\HiroInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BotTest extends TestCase
{
    public function testVersionConstantsAreDefined(): void
    {
        $this->assertNotEmpty(Version::VERSION);
        $this->assertStringStartsWith('v', Version::VERSION);
        $this->assertContains(Version::TYPE, ['development', 'production']);
    }

    public function testHiroImplementsHiroInterface(): void
    {
        $this->assertTrue(is_a(Hiro::class, HiroInterface::class, true));
    }

    #[DataProvider('formatNumberProvider')]
    public function testFormatNumber(int $input, int|string $expected): void
    {
        $this->assertSame($expected, Hiro::formatNumber($input));
    }

    public static function formatNumberProvider(): array
    {
        return [
            'zero' => [0, 0],
            'below thousand' => [999, 999],
            'exact thousand' => [1000, '1k'],
            'thousands with decimals' => [1550, '1.55k'],
            'just below million' => [999999, '1000k'],
            'exact million' => [1000000, '1kk'],
            'millions with decimals' => [2500000, '2.5kk'],
            'exact billion' => [1000000000, '1kkk'],
            'billions with decimals' => [1234567890, '1.23kkk'],
        ];
    }
}
