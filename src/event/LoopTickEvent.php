<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use DateTimeImmutable;
use kuaukutsu\poc\cron\EventInterface;

/**
 * @psalm-immutable
 */
final class LoopTickEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(public readonly DateTimeImmutable $tick)
    {
        $this->message = 'tick: ' . $this->formatTick();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    private function formatTick(): string
    {
        return $this->tick->format('c');
    }
}
