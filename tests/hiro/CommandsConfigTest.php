<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\Hiro;
use hiro\commands\Command;
use hiro\parts\CommandLoader;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;

final class CommandsConfigTest extends TestCase
{
    public function testAllCommandClassesConfigureValidMetadataWithoutConflicts(): void
    {
        $commandsDir = dirname(__DIR__, 2) . '/src/commands';
        $loop = $this->createStub(LoopInterface::class);
        $client = $this->createStub(Hiro::class);
        $client->method('getLoop')->willReturn($loop);
        $loader = $this->createStub(CommandLoader::class);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($commandsDir, \FilesystemIterator::SKIP_DOTS)
        );

        $seenCommands = [];
        $seenAliases = [];
        $loadedCount = 0;

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $file->getBasename('.php');
            if ($className === 'Command') {
                continue;
            }

            require_once $file->getPathname();
            $fqcn = 'hiro\\commands\\' . $className;
            $this->assertTrue(class_exists($fqcn), "Expected command class {$fqcn} to exist");

            $cmd = new $fqcn($client, $loader);
            $this->assertInstanceOf(Command::class, $cmd);
            $this->assertNotEmpty($cmd->command, "{$fqcn} must define a command name");
            $this->assertSame(strtolower($cmd->command), $cmd->command, "{$fqcn} command name must be lowercase");
            $this->assertContains(
                $cmd->category,
                ['author', 'economy', 'music', 'reactions', 'utility'],
                "{$fqcn} has unexpected category '{$cmd->category}'"
            );
            $this->assertNotEmpty($cmd->description, "{$fqcn} must define a description");

            $this->assertArrayNotHasKey($cmd->command, $seenCommands, "Duplicate command name '{$cmd->command}' in {$fqcn}");
            $seenCommands[$cmd->command] = $fqcn;

            foreach ($cmd->aliases as $alias) {
                $this->assertArrayNotHasKey($alias, $seenAliases, "Duplicate alias '{$alias}' in {$fqcn}");
                $seenAliases[$alias] = $fqcn;
            }

            $loadedCount++;
        }

        $this->assertGreaterThanOrEqual(30, $loadedCount);

        foreach ($seenAliases as $alias => $aliasOwner) {
            $this->assertArrayNotHasKey(
                $alias,
                $seenCommands,
                "Alias '{$alias}' in {$aliasOwner} collides with command name in " . ($seenCommands[$alias] ?? '')
            );
        }
    }
}
