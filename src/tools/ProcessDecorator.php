<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tools;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\ProcessInterface;

/**
 * @psalm-immutable
 */
final class ProcessDecorator implements ProcessInterface
{
    private readonly Process $process;

    public function __construct(
        private readonly array $command,
        private readonly float $timeout = 300.,
    ) {
        /** @psalm-suppress ImpureFunctionCall */
        $this->process = new Process(
            $this->command,
            null,
            getenv(),
            null,
            $this->timeout,
        );
    }

    public function getName(): string
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->process->getCommandLine();
    }

    public function getProcess(): Process
    {
        return $this->process;
    }
}
