<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use DateTimeImmutable;
use kuaukutsu\poc\cron\EventInterface;

/**
 * @psalm-immutable
 */
final class LoopTimeoutEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(public readonly DateTimeImmutable $time)
    {
        $this->message = 'timeout: ' . $this->time->format('c');
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
