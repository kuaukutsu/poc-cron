<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use kuaukutsu\poc\cron\EventInterface;

final class LoopExitEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(public readonly int $signal)
    {
        $this->message = "[$this->signal] signal exit";
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
