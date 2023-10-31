<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tests;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\cron\SchedulerTimer;

final class SchedulerTimerWeekTest extends TestCase
{
    public function testWeekly(): void
    {
        $timer = SchedulerTimer::weekly(1);
        $tick = new DateTimeImmutable('last Monday May');
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

        self::assertFalse(
            $timer->run($tick->modify('+1 days'))
        );

        self::assertFalse(
            $timer->run($tick->modify('+1 months'))
        );

        self::assertTrue(
            $timer->run($tick->modify('+21 days'))
        );

        $tick = new DateTimeImmutable('last Thursday');
        self::assertTrue(
            SchedulerTimer::weekly(4)->run($tick)
        );

        $tick = new DateTimeImmutable('last Friday');
        self::assertTrue(
            SchedulerTimer::weekly(5)->run($tick)
        );
    }

    public function testWeekdays(): void
    {
        $timer = SchedulerTimer::weekdays();

        $tick = new DateTimeImmutable('2023-10-25 00:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertFalse(
            $timer->run($tick->modify('+2 hours'))
        );

        $tick = new DateTimeImmutable('2023-10-10 00:00:20');
        self::assertTrue(
            $timer->run($tick)
        );

        self::assertTrue(
            $timer->run($tick->modify('+2 days'))
        );

        $tick = new DateTimeImmutable('2023-10-14 00:00:20');
        self::assertFalse(
            $timer->run($tick)
        );

        $tick = new DateTimeImmutable('2023-10-15 00:00:20');
        self::assertFalse(
            $timer->run($tick)
        );
    }

    public function testWeekend(): void
    {
        $timer = SchedulerTimer::weekend();

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

    public function testWeeklyLessAssert(): void
    {
        $this->expectException(LogicException::class);

        SchedulerTimer::weekly(0);
    }

    public function testWeeklyGreatAssert(): void
    {
        $this->expectException(LogicException::class);

        SchedulerTimer::weekly(8);
    }
}
