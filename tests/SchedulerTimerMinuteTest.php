<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\cron\SchedulerTimer;

final class SchedulerTimerMinuteTest extends TestCase
{
    public function testEveryMinute(): void
    {
        $timer = SchedulerTimer::everyMinute();
        $tick = new DateTimeImmutable('2023-10-25 10:33:20');

        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+10 seconds'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+2 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+11 minute'))
        );
    }

    public function testEveryFiveMinute(): void
    {
        $timer = SchedulerTimer::everyFiveMinutes();
        $tick = new DateTimeImmutable('2023-10-25 10:33:20');

        self::assertFalse(
            $timer->run($tick)
        );

        // 2023-10-25 10:35:20
        self::assertTrue(
            $timer->run($tick->modify('+2 minute'))
        );

        // 2023-10-25 10:33:30
        self::assertFalse(
            $timer->run($tick->modify('+10 seconds'))
        );

        self::assertFalse(
            $timer->run($tick->modify('+3 minute'))
        );

        // 2023-10-25 10:45:20
        self::assertTrue(
            $timer->run($tick->modify('+12 minute'))
        );
    }

    public function testEveryTenMinute(): void
    {
        $timer = SchedulerTimer::everyTenMinutes();
        $tick = new DateTimeImmutable('2023-10-25 10:35:20');

        self::assertFalse(
            $timer->run($tick)
        );

        $tick = new DateTimeImmutable('2023-10-25 10:40:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+3 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+10 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+10 days'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 months'))
        );
    }

    public function testEveryEvenMinute(): void
    {
        $timer = SchedulerTimer::everyEvenMinutes();

        $tick = new DateTimeImmutable('2023-10-25 10:34:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+10 second'))
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );

        $tick = new DateTimeImmutable('2023-10-25 00:02:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );

        $tick = new DateTimeImmutable('2023-10-25 21:00:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );
        self::assertTrue(
            $timer->run($tick->modify('+2 minute'))
        );
    }

    public function testEveryOddMinute(): void
    {
        $timer = SchedulerTimer::everyOddMinutes();

        $tick = new DateTimeImmutable('2023-10-25 10:35:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+10 second'))
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );

        $tick = new DateTimeImmutable('2023-10-25 00:01:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );

        $tick = new DateTimeImmutable('2023-10-25 21:59:20');
        self::assertTrue(
            $timer->run($tick)
        );
        self::assertFalse(
            $timer->run($tick->modify('+1 minute'))
        );
        self::assertTrue(
            $timer->run($tick->modify('+2 minute'))
        );
    }
}
