<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Closure;
use DateTimeInterface;

final class SchedulerTimer
{
    private ?DateTimeInterface $timestamp = null;

    /**
     * @param Closure(self $self, DateTimeInterface $dateTime): bool $acton
     */
    private function __construct(private readonly Closure $acton)
    {
    }

    public function run(DateTimeInterface $dateTime): bool
    {
        $action = $this->acton;
        return $action($this, $dateTime);
    }

    /**
     * Schedule the command at a given time.
     *
     * @param DateTimeInterface $time
     * @return $this
     */
    public function at(DateTimeInterface $time): self
    {
        return new self(
            function (self $self, DateTimeInterface $tick) use ($time): bool {
                if ($self->timestamp === null && $time->format('YmdHi') === $tick->format('YmdHi')) {
                    $self->timestamp = $tick;
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Schedule the event to run every N hours.
     *
     * @param int $hours
     * @return $this
     */
    public static function everyNHours(int $hours): self
    {
        return new self(
            function (self $self, DateTimeInterface $tick) use ($hours): bool {
                if (
                    $self->timestamp !== null
                    && $self->timestamp->format('YmdH') === $tick->format('YmdH')
                ) {
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
     * Schedule the event to run every N days.
     *
     * @param int $days
     * @return $this
     */
    public static function everyNDays(int $days): self
    {
        return new self(
            function (self $self, DateTimeInterface $tick) use ($days): bool {
                if (
                    $self->timestamp !== null
                    && $self->timestamp->format('Ymd') === $tick->format('Ymd')
                ) {
                    return false;
                }

                if (
                    $tick->format('H') === '00'
                    && $tick->format('i') === '00'
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
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public static function monthly(): self
    {
        return new self(
            function (self $self, DateTimeInterface $tick): bool {
                if (
                    $self->timestamp !== null
                    && $self->timestamp->format('Ym') === $tick->format('Ym')
                ) {
                    return false;
                }

                if (
                    $tick->format('d') === '01'
                    && $tick->format('H') === '00'
                    && $tick->format('i') === '00'
                ) {
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
            function (self $self, DateTimeInterface $tick): bool {
                if (
                    $self->timestamp !== null
                    && $self->timestamp->format('Ymd') === $tick->format('Ymd')
                ) {
                    return false;
                }

                if (
                    $tick->format('N') > 5
                    && $tick->format('H') === '00'
                    && $tick->format('i') === '00'
                ) {
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
        return new self(
            function (self $self, DateTimeInterface $tick) use ($minutes): bool {
                if (
                    $self->timestamp !== null
                    && $self->timestamp->format('YmdHi') === $tick->format('YmdHi')
                ) {
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
}
