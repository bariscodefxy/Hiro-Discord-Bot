<?php

declare(strict_types=1);

namespace hiro\tests;

use Discord\Parts\User\Activity;
use hiro\Hiro;
use hiro\interfaces\PresenceManagerInterface;
use hiro\parts\PresenceManager;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;

final class PresenceManagerTest extends TestCase
{
    public function testFluentConfigurationMethods(): void
    {
        $discord = $this->createStub(Hiro::class);
        $manager = new PresenceManager($discord);

        $this->assertInstanceOf(PresenceManagerInterface::class, $manager);
        $this->assertSame($discord, $manager->discord);
        $this->assertSame(Activity::TYPE_WATCHING, $manager->presenceType);

        $result = $manager
            ->setLoopTime(15.0)
            ->setPresenceType(Activity::TYPE_PLAYING)
            ->setPresences(['First'])
            ->addPresence('Second');

        $this->assertSame($manager, $result);
        $this->assertSame(15.0, $manager->looptime);
        $this->assertSame(Activity::TYPE_PLAYING, $manager->presenceType);
        $this->assertSame(['First', 'Second'], $manager->presences);
    }

    public function testStartThreadUpdatesPresenceAndCyclesWithTimer(): void
    {
        $capturedCallback = null;
        $loop = $this->createMock(LoopInterface::class);
        $loop->expects($this->once())
            ->method('addPeriodicTimer')
            ->with(10.0, $this->isCallable())
            ->willReturnCallback(function (float $interval, callable $cb) use (&$capturedCallback) {
                $capturedCallback = $cb;
                return $this->createStub(\React\EventLoop\TimerInterface::class);
            });

        $updatedActivities = [];
        $discord = $this->createMock(Hiro::class);
        $discord->method('getLoop')->willReturn($loop);
        $discord->method('factory')
            ->willReturnCallback(function (string $class, array $data) use ($discord) {
                return new Activity($discord, $data);
            });
        $discord->expects($this->exactly(3))
            ->method('updatePresence')
            ->willReturnCallback(function (Activity $act, bool $afk, string $status) use (&$updatedActivities) {
                $updatedActivities[] = [$act->name, $act->type, $afk, $status];
            });

        $manager = new PresenceManager($discord);
        $manager->setLoopTime(10.0)
            ->setPresenceType(Activity::TYPE_LISTENING)
            ->setPresences(['Status A', 'Status B'])
            ->startThread();

        // Trigger periodic timer twice to verify wrap-around from Status B back to Status A
        ($capturedCallback)();
        ($capturedCallback)();

        $this->assertSame([
            ['Status A', Activity::TYPE_LISTENING, false, 'online'],
            ['Status B', Activity::TYPE_LISTENING, false, 'online'],
            ['Status A', Activity::TYPE_LISTENING, false, 'online'],
        ], $updatedActivities);
    }
}
