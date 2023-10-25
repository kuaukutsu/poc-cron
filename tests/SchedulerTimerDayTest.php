<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\cron\SchedulerTimer;

final class SchedulerTimerDayTest extends TestCase
{
    public function testDayly(): void
    {
        $timer = SchedulerTimer::daily();
        $tick = new DateTimeImmutable('2023-10-25 00:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+2 minute'))
        );

        self::assertFalse(
            $timer->run($tick->modify('+1 hours'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 days'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 months'))
        );
    }

    public function testEveryDayAt(): void
    {
        $timer = SchedulerTimer::everyDayAt(2, 15);

        $tick = new DateTimeImmutable('2023-10-25 02:15:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+20 seconds'))
        );

        $tick = new DateTimeImmutable('2023-10-28 02:15:35');
        self::assertTrue(
            $timer->run($tick)
        );
    }
}
