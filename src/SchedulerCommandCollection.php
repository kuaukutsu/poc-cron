<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use kuaukutsu\ds\collection\Collection;

/**
 * @extends Collection<SchedulerCommand>
 */
final class SchedulerCommandCollection extends Collection
{
    public function getType(): string
    {
        return SchedulerCommand::class;
    }
}
