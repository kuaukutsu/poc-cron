<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\tools\ProcessUuid;

/**
 * @psalm-immutable
 */
final class SchedulerCommand
{
    private readonly ProcessUuid $uuid;

    private readonly Process $process;

    public function __construct(
        ProcessInterface $process,
        private readonly SchedulerTimer $timer,
    ) {
        $this->uuid = $process->getUuid();
        $this->process = $process->getProcess();
    }

    public function getUuid(): string
    {
        return $this->uuid->toString();
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getTimer(): SchedulerTimer
    {
        return $this->timer;
    }
}
