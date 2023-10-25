<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use kuaukutsu\poc\cron\EventInterface;

final class ProcessBufferEvent implements EventInterface
{
    public function __construct(private readonly string $buffer)
    {
    }

    public function getMessage(): string
    {
        return $this->buffer;
    }
}
