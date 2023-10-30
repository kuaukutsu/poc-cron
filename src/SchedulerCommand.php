<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Symfony\Component\Process\Process;

/**
 * @psalm-immutable
 */
final class SchedulerCommand
{
    private readonly string $uuid;

    private readonly Process $process;

    public function __construct(
        ProcessInterface $process,
        private readonly SchedulerTimer $timer,
    ) {
        $this->process = $process->getProcess();

        /** @psalm-suppress ImpureMethodCall */
        $this->uuid = preg_replace(
            '~^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$~',
            '\1-\2-\3-\4-\5',
            hash('md5', $this->process->getCommandLine())
        );
    }

    public function getId(): string
    {
        return $this->uuid;
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
