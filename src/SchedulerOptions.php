<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

/**
 * @psalm-immutable
 */
final class SchedulerOptions
{
    /**
     * @param positive-int $tack in Second
     * @param positive-int $keeperInterval in Second
     * @param int[] $signalsInterrupt
     * @param positive-int|null $timeout in Second
     */
    public function __construct(
        private readonly int $tack = 5,
        private readonly int $keeperInterval = 2,
        public readonly array $signalsInterrupt = [SIGHUP, SIGINT, SIGTERM],
        public readonly ?int $timeout = null,
    ) {
    }

    public function getRunnerInterval(): int
    {
        return max(1, $this->tack);
    }

    public function getKeeperInterval(): int
    {
        return max(1, $this->keeperInterval);
    }
}
