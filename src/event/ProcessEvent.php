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

    /** @psalm-suppress ImpureMethodCall */
    public function getStatus(): string
    {
        return $this->process->getStatus();
    }

    /** @psalm-suppress ImpureMethodCall */
    public function getOutput(): string
    {
        if ($this->process->isSuccessful()) {
            return $this->process->getOutput();
        }

        $output = $this->process->getOutput();
        if ($output === '') {
            $output = $this->process->getErrorOutput();
        }

        return $output;
    }

    /** @psalm-suppress ImpureMethodCall */
    public function getCommand(): string
    {
        return $this->process->getCommandLine();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
