<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

/**
 * @psalm-immutable
 */
final class SchedulerOptions
{
    /**
     * @param float $interval in Seconds
     * @param float $keeperInterval in Seconds
     * @param float|null $timeout Event Loop Timeout in Second
     * @param int[] $interruptSignals A POSIX signal
     * @param float $interruptTimeout The timeout in seconds
     */
    public function __construct(
        private readonly float $interval = 60.,
        private readonly float $keeperInterval = 5.,
        public readonly ?float $timeout = null,
        public readonly array $interruptSignals = [SIGHUP, SIGINT, SIGTERM],
        private readonly float $interruptTimeout = 10.,
    ) {
    }

    public function getRunnerInterval(): float
    {
        return max(1., $this->interval);
    }

    public function getKeeperInterval(): float
    {
        return max(1., $this->keeperInterval);
    }

    public function getInterruptTimeout(): float
    {
        return max(1., $this->interruptTimeout);
    }
}
