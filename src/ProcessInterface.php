<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\tools\ProcessUuid;

/**
 * Wrapper Sympony/Process
 *
 * @psalm-immutable
 */
interface ProcessInterface
{
    public function getUuid(): ProcessUuid;

    public function getProcess(): Process;
}
