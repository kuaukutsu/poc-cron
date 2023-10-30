<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\event;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\EventInterface;

/**
 * @psalm-immutable
 */
final class ProcessTimeoutEvent implements EventInterface
{
    public function __construct(
        public readonly string $commandId,
        private readonly Process $process,
        private readonly string $message,
    ) {
    }

    public function getStatus(): string
    {
        return Process::STATUS_TERMINATED;
    }

    /** @psalm-suppress ImpureMethodCall */
    public function getOutput(): string
    {
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
