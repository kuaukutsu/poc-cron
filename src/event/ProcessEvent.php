<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\EventInterface;

final class ProcessEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(
        public readonly string $commandId,
        private readonly Process $process,
    ) {
        $this->message = "[$this->commandId] " . $this->process->getCommandLine();
    }

    public function getStatus(): string
    {
        return $this->process->getStatus();
    }

    public function getCommand(): string
    {
        return $this->process->getCommandLine();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
