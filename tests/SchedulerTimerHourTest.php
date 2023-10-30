<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\cron\SchedulerTimer;

final class SchedulerTimerHourTest extends TestCase
{
    public function testHourly(): void
    {
        $timer = SchedulerTimer::hourly();

        $tick = new DateTimeImmutable('2023-10-25 10:35:20');
        self::assertFalse(
            $timer->run($tick)
        );

        $tick = new DateTimeImmutable('2023-10-25 10:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+2 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 hours'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+5 hours'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 days'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 months'))
        );
    }

    public function testEveryFiveHours(): void
    {
        $timer = SchedulerTimer::everyNHours(5);

        $tick = new DateTimeImmutable('2023-10-25 10:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+1 hours'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+5 hours'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+5 days'))
        );
    }

    public function testHourlyAt(): void
    {
        $timer = SchedulerTimer::hourlyAt(15);
        $tick = new DateTimeImmutable('2023-10-25 02:15:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+20 seconds'))
        );

        self::assertFalse(
            $timer->run($tick->modify('+5 minute'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 hour'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 day'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+1 month'))
        );

        $timer = SchedulerTimer::hourlyAt(5);
        $tick = new DateTimeImmutable('2023-10-25 18:05:20');
        self::assertTrue(
            $timer->run($tick)
        );
    }
}
