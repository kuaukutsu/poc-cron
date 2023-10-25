<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\EventInterface;

/**
 * @psalm-immutable
 */
final class ProcessEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(
        public readonly string $commandId,
        private readonly Process $process,
    ) {
        $this->message = "[$this->commandId] " . $this->getCommand();
    }

    public function getStatus(): string
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->process->getStatus();
    }

    public function getCommand(): string
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->process->getCommandLine();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
