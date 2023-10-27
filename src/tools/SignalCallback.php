<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tools;

use Closure;

final class SignalCallback
{
    public function __construct(
        public readonly string $id,
        public readonly int $signal,
        private readonly Closure $callback,
    ) {
    }

    public function call(): void
    {
        call_user_func($this->callback, $this->signal);
    }
}
