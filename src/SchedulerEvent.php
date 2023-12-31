<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

enum SchedulerEvent: string
{
    case LoopExit = 'loop-exit-event';

    case LoopTick = 'loop-tick-event';

    case LoopTimeout = 'loop-timeout-event';

    case ProcessPull = 'process-pull-event';

    case ProcessPush = 'process-push-event';

    case ProcessExists = 'process-exists-event';

    case ProcessStop = 'process-stop-event';

    case ProcessSuccess = 'process-success-event';

    case ProcessError = 'process-error-event';

    case ProcessState = 'process-state-event';

    case ProcessTimeout = 'process-timeout-event';
}
