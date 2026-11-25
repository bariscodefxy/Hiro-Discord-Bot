<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\database\Database;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    private array $backupEnv = [];

    protected function setUp(): void
    {
        foreach (['DB_USER', 'DB_PASS', 'DB_HOST', 'DB_NAME'] as $key) {
            if (array_key_exists($key, $_ENV)) {
                $this->backupEnv[$key] = $_ENV[$key];
                unset($_ENV[$key]);
            }
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->backupEnv as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    public function testLeavesIsConnectedFalseWhenEnvVarsMissing(): void
    {
        $db = new Database();

        $this->assertFalse($db->isConnected);
    }
}
