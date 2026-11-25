<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\parts\Padding;
use PHPUnit\Framework\TestCase;

final class PaddingTest extends TestCase
{
    public function testRightPaddingWithAsciiAndMultibyte(): void
    {
        $this->assertSame('abc  ', Padding::mb_str_pad('abc', 5));
        $this->assertSame('şğü  ', Padding::mb_str_pad('şğü', 5));
        $this->assertSame(5, mb_strlen(Padding::mb_str_pad('şğü', 5), 'UTF-8'));
    }

    public function testLeftPadding(): void
    {
        $this->assertSame('007', Padding::mb_str_pad('7', 3, '0', STR_PAD_LEFT));
        $this->assertSame('☆☆あ', Padding::mb_str_pad('あ', 3, '☆', STR_PAD_LEFT));
    }

    public function testBothSidePadding(): void
    {
        $this->assertSame('--hi--', Padding::mb_str_pad('hi', 6, '-', STR_PAD_BOTH));
        $this->assertSame('-hi--', Padding::mb_str_pad('hi', 5, '-', STR_PAD_BOTH));
    }

    public function testMultiCharacterPadStringCyclesProperly(): void
    {
        $this->assertSame('x-=-=', Padding::mb_str_pad('x', 5, '-=', STR_PAD_RIGHT));
    }

    public function testReturnsOriginalWhenLengthNotGreaterThanInput(): void
    {
        $this->assertSame('hello', Padding::mb_str_pad('hello', 5));
        $this->assertSame('hello', Padding::mb_str_pad('hello', 3));
        $this->assertSame('hello', Padding::mb_str_pad('hello', 0));
        $this->assertSame('hello', Padding::mb_str_pad('hello', -2));
    }
}
