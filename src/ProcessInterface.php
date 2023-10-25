<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Symfony\Component\Process\Process;

/**
 * @psalm-immutable
 */
interface ProcessInterface
{
    public function getName(): string;

    public function getProcess(): Process;
}
