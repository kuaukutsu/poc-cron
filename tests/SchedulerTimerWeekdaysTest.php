<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\cron\SchedulerTimer;

final class SchedulerTimerWeekdaysTest extends TestCase
{
    public function testWeekdays(): void
    {
        $timer = SchedulerTimer::weekdays();

        $tick = new DateTimeImmutable('2023-10-25 10:35:20');
        self::assertFalse(
            $timer->run($tick)
        );

        $tick = new DateTimeImmutable('2023-10-14 00:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+2 hours'))
        );

        $tick = new DateTimeImmutable('2023-10-15 00:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+10 hours'))
        );

        self::assertFalse(
            $timer->run($tick->modify('+1 days'))
        );
    }
}
