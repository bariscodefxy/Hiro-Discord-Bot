<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\parts\ArgumentParser;
use PHPUnit\Framework\TestCase;

final class ArgumentParserTest extends TestCase
{
    public function testDefaultsWhenNoShardFlagsProvided(): void
    {
        $parser = new ArgumentParser(['bot.php']);

        $this->assertSame(0, $parser->getShardId());
        $this->assertSame(1, $parser->getShardCount());
    }

    public function testParsesExplicitShardIdAndCount(): void
    {
        $parser = new ArgumentParser(['bot.php', '--shard-id', '2', '--shard-count', '4']);

        $this->assertSame('2', $parser->getShardId());
        $this->assertSame('4', $parser->getShardCount());
    }

    public function testParsesFlagsInReverseOrder(): void
    {
        $parser = new ArgumentParser(['bot.php', '--shard-count', '8', '--shard-id', '5']);

        $this->assertSame('5', $parser->getShardId());
        $this->assertSame('8', $parser->getShardCount());
    }

    public function testReturnsNullWhenArgsArrayIsEmpty(): void
    {
        $parser = new ArgumentParser([]);

        $this->assertNull($parser->getShardId());
        $this->assertNull($parser->getShardCount());
    }
}
