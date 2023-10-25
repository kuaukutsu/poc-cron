<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use DateTimeInterface;
use kuaukutsu\poc\cron\EventInterface;

final class LoopTickEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(public readonly DateTimeInterface $tick)
    {
        $this->message = 'tick: ' . $this->tick->format('c');
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
