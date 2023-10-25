<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

interface EventInterface
{
    public function getMessage(): string;
}
