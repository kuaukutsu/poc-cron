<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Closure;
use DateTimeImmutable;
use LogicException;

final class SchedulerTimer
{
    private ?DateTimeImmutable $timestamp = null;

    /**
     * @param Closure(self $self, DateTimeImmutable $dateTime): bool $acton
     */
    private function __construct(private readonly Closure $acton)
    {
    }

    public function run(DateTimeImmutable $dateTime): bool
    {
        $action = $this->acton;
        return $action($this, $dateTime);
    }

    /**
     * Schedule the event to run every N hours.
     *
     * @param positive-int $hours
     * @return $this
     */
    public static function everyNHours(int $hours): self
    {
        self::assertEveryHour($hours);

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($hours): bool {
                if ($self->timestamp?->format('YmdH') === $tick->format('YmdH')) {
                    return false;
                }

                if ($tick->format('i') === '00' && ((int)$tick->format('G') % $hours) === 0) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public static function hourly(): self
    {
        return self::everyNHours(1);
    }

    /**
     * Schedule the event to run hourly.
     *
     * @param int $minute 0-59
     * @return $this
     */
    public static function hourlyAt(int $minute): self
    {
        self::assertMinute($minute);

        $time = (new DateTimeImmutable('1999-01-01 00:00:01'))
            ->modify("+$minute minutes");

        if ($time === false) {
            throw new LogicException('time must implement DateTimeImmutable.');
        }

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($time): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if ($time->format('i') === $tick->format('i')) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every N days.
     *
     * @return $this
     */
    public static function everyNDays(int $days): self
    {
        self::assertDay($days);

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($days): bool {
                if ($self->timestamp?->format('Ymd') === $tick->format('Ymd')) {
                    return false;
                }

                if (
                    $tick->format('Hi') === '0000'
                    && ((int)$tick->format('j') % $days) === 0
                ) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public static function daily(): self
    {
        return self::everyNDays(1);
    }

    /**
     * Schedule the event to run daily AT.
     *
     * @param int $hour 0 - 23
     * @param int $minute 0 - 59
     * @return $this
     */
    public static function dailyAt(int $hour, int $minute = 0): self
    {
        self::assertHour($hour);
        self::assertMinute($minute);

        $time = (new DateTimeImmutable('1999-01-01 00:00:01'))
            ->modify("+$hour hours $minute minutes");

        if ($time === false) {
            throw new LogicException('time must implement DateTimeImmutable.');
        }

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($time): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if ($time->format('Hi') === $tick->format('Hi')) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public static function monthly(): self
    {
        return new self(
            function (self $self, DateTimeImmutable $tick): bool {
                if ($self->timestamp?->format('Ym') === $tick->format('Ym')) {
                    return false;
                }

                if ($tick->format('dHi') === '010000') {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run daily AT.
     *
     * @return $this
     */
    public static function monthlyAt(int $hour, int $minute): self
    {
        self::assertHour($hour);
        self::assertMinute($minute);

        $time = (new DateTimeImmutable('1999-01-01 00:00:01'))
            ->modify("+ $hour hours $minute minutes");

        if ($time === false) {
            throw new LogicException('time must implement DateTimeImmutable.');
        }

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($time): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if ($time->format('dHi') === $tick->format('dHi')) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run only on weekly.
     *
     * @param int $day ISO 8601 numeric representation of the day of the week. 1 (for Monday) through 7 (for Sunday)
     * @return $this
     */
    public static function weekly(int $day = 7): self
    {
        self::assertDayOfWeek($day);

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($day): bool {
                if ($self->timestamp?->format('Ymd') === $tick->format('Ymd')) {
                    return false;
                }

                if ($tick->format('NHi') === $day . '0000') {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public static function weekdays(): self
    {
        return new self(
            function (self $self, DateTimeImmutable $tick): bool {
                if ($self->timestamp?->format('Ymd') === $tick->format('Ymd')) {
                    return false;
                }

                if ($tick->format('N') < 6 && $tick->format('Hi') === '0000') {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run only on weekend.
     *
     * @return $this
     */
    public static function weekend(): self
    {
        return new self(
            function (self $self, DateTimeImmutable $tick): bool {
                if ($self->timestamp?->format('Ymd') === $tick->format('Ymd')) {
                    return false;
                }

                if ($tick->format('N') > 5 && $tick->format('Hi') === '0000') {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every N minutes.
     *
     * @param positive-int $minutes
     * @return $this
     */
    public static function everyNMinutes(int $minutes): self
    {
        self::assertEveryMinute($minutes);

        return new self(
            function (self $self, DateTimeImmutable $tick) use ($minutes): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if (((int)$tick->format('i') % $minutes) === 0) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every even minutes.
     *
     * @return $this
     */
    public static function everyEvenMinutes(): self
    {
        return new self(
            function (self $self, DateTimeImmutable $tick): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if (((int)$tick->format('i') % 2) === 0) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every doo minutes.
     *
     * @return $this
     */
    public static function everyOddMinutes(): self
    {
        return new self(
            function (self $self, DateTimeImmutable $tick): bool {
                if ($self->timestamp?->format('YmdHi') === $tick->format('YmdHi')) {
                    return false;
                }

                if (((int)$tick->format('i') % 2) > 0) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return $this
     */
    public static function everyMinute(): self
    {
        return self::everyNMinutes(1);
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return $this
     */
    public static function everyFiveMinutes(): self
    {
        return self::everyNMinutes(5);
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return $this
     */
    public static function everyTenMinutes(): self
    {
        return self::everyNMinutes(10);
    }

    private static function assertMinute(int $value): void
    {
        if ($value < 0 || $value > 59) {
            throw new LogicException('Minute must be in the range from 1 to 59');
        }
    }

    private static function assertEveryMinute(int $value): void
    {
        if ($value < 1 || $value > 55) {
            throw new LogicException('Minute must be in the range from 1 to 55');
        }
    }

    private static function assertHour(int $value): void
    {
        if ($value < 0 || $value > 23) {
            throw new LogicException('Hour must be in the range from 1 to 23');
        }
    }

    private static function assertEveryHour(int $value): void
    {
        if ($value < 1 || $value > 23) {
            throw new LogicException('Hour must be in the range from 1 to 23');
        }
    }

    private static function assertDay(int $value): void
    {
        if ($value < 1 || $value > 31) {
            throw new LogicException('Day must be in the range from 1 to 31');
        }
    }

    private static function assertDayOfWeek(int $value): void
    {
        if ($value < 1 || $value > 7) {
            throw new LogicException('Day of the Week must be in the range from 1 (for Monday) to 7 (for Sunday)');
        }
    }
}
