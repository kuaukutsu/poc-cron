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

    private readonly Process $proccess;

    public function __construct(
        ProcessInterface $proccess,
        private readonly SchedulerTimer $timer,
    ) {
        $this->proccess = $proccess->getProcess();

        /**
         * @psalm-suppress ImpureMethodCall
         */
        $this->uuid = preg_replace(
            '~^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$~',
            '\1-\2-\3-\4-\5',
            hash('md5', $this->proccess->getCommandLine())
        );
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getProcess(): Process
    {
        return $this->proccess;
    }

    public function getTimer(): SchedulerTimer
    {
        return $this->timer;
    }
}
