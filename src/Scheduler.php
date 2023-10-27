<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Closure;
use DateTimeImmutable;
use Revolt\EventLoop;
use Revolt\EventLoop\UnsupportedFeatureException;
use RuntimeException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use kuaukutsu\poc\cron\event\LoopExitEvent;
use kuaukutsu\poc\cron\event\LoopTickEvent;
use kuaukutsu\poc\cron\event\LoopTimeoutEvent;
use kuaukutsu\poc\cron\event\ProcessEvent;
use kuaukutsu\poc\cron\event\ProcessBufferEvent;
use kuaukutsu\poc\cron\event\ProcessTimeoutEvent;

final class Scheduler implements EventPublisherInterface
{
    use SchedulerPublisherEvent;

    /**
     * @var array<string, Process>
     */
    private array $processesActive = [];

    /**
     * @var Closure(string $callbackId): void
     */
    private readonly Closure $fnRunner;

    /**
     * @var Closure(string $callbackId): void
     */
    private readonly Closure $fnKeeper;

    /**
     * A unique identifier that can be used to cancel, enable or disable the callback.
     */
    private ?string $runnerId = null;

    /**
     * A unique identifier that can be used to cancel, enable or disable the callback.
     */
    private ?string $keeperId = null;

    public function __construct(SchedulerCommand ...$commands)
    {
        $this->fnRunner = function () use ($commands): void {
            $tick = new DateTimeImmutable();
            $this->trigger(
                SchedulerEvent::LoopTick,
                new LoopTickEvent($tick)
            );

            foreach ($commands as $command) {
                if (
                    $command->getTimer()->run($tick) === false
                    || $this->processExists($command->getId())
                ) {
                    continue;
                }

                $process = $command->getProcess();
                $process->start($this->handlerProcess());
                $this->processPush($command->getId(), $process);
            }
        };

        $this->fnKeeper = function (): void {
            if ($this->processesActive === []) {
                $this->keeperDisable();
                return;
            }

            foreach ($this->processesActive as $id => $process) {
                if ($process->isRunning() === false) {
                    $this->processPull($id, $process);
                    unset($process);
                    continue;
                }

                try {
                    $process->checkTimeout();
                } catch (ProcessTimedOutException $exception) {
                    $this->trigger(
                        SchedulerEvent::ProcessTimeout,
                        new ProcessTimeoutEvent($id, $process, $exception->getMessage())
                    );

                    $this->processPull($id, $process);
                    unset($process);
                    continue;
                }

                $this->trigger(
                    SchedulerEvent::ProcessState,
                    new ProcessEvent($id, $process)
                );
            }
        };
    }

    /**
     * @throws UnsupportedFeatureException
     */
    public function run(SchedulerOptions $options = new SchedulerOptions()): void
    {
        $this->runnerId = EventLoop::repeat(
            $options->getRunnerInterval(),
            $this->fnRunner
        );

        $this->keeperId = EventLoop::repeat(
            $options->getKeeperInterval(),
            $this->fnKeeper
        );
        $this->keeperDisable();

        $this->onSignals($options->signalsInterrupt);
        if ($options->timeout > 0) {
            $this->onTimeout($options->timeout);
        }

        EventLoop::run();
    }

    /**
     * @param int[] $signals ext-pcntl
     * @throws UnsupportedFeatureException
     */
    private function onSignals(array $signals): void
    {
        foreach ($signals as $signal) {
            EventLoop::onSignal($signal, function () use ($signal): void {
                $this->loopExit($signal);
            });
        }
    }

    /**
     * @param positive-int $timeout
     */
    private function onTimeout(int $timeout): void
    {
        EventLoop::delay($timeout, function (): void {
            $this->trigger(
                SchedulerEvent::LoopTimeout,
                new LoopTimeoutEvent(new DateTimeImmutable())
            );

            $this->loopExit(SIGTERM);
        });
    }

    private function keeperDisable(): void
    {
        if ($this->keeperId === null) {
            throw new RuntimeException(
                'A Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        EventLoop::disable($this->keeperId);
    }

    private function keeperEnable(): void
    {
        if ($this->keeperId === null) {
            throw new RuntimeException(
                'A Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        EventLoop::enable($this->keeperId);
    }

    private function loopExit(int $signal): void
    {
        if ($this->runnerId === null || $this->keeperId === null) {
            throw new RuntimeException(
                'A Runner/Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        foreach ($this->processesActive as $id => $process) {
            if ($process->isRunning() || $process->isStarted()) {
                try {
                    $this->trigger(
                        SchedulerEvent::ProcessStop,
                        new ProcessEvent($id, $process)
                    );

                    $process->stop(5, $signal);
                } catch (LogicException) {
                    // Cannot send signal on a non-running process.
                }
            }
        }

        $this->trigger(
            SchedulerEvent::LoopExit,
            new LoopExitEvent($signal)
        );

        EventLoop::cancel($this->runnerId);
        EventLoop::cancel($this->keeperId);

        exit($signal);
    }

    private function handlerProcess(): Closure
    {
        return function (string $type, string $buffer): void {
            $this->trigger(
                $type === Process::ERR ? SchedulerEvent::ProcessStdErr : SchedulerEvent::ProcessStdOut,
                new ProcessBufferEvent($buffer)
            );
        };
    }

    private function processExists(string $id): bool
    {
        if (array_key_exists($id, $this->processesActive)) {
            $this->trigger(
                SchedulerEvent::ProcessExists,
                new ProcessEvent($id, $this->processesActive[$id])
            );

            return true;
        }

        return false;
    }

    private function processPush(string $id, Process $process): void
    {
        if ($this->processesActive === []) {
            $this->keeperEnable();
        }

        $this->processesActive[$id] = $process;
        $this->trigger(
            SchedulerEvent::ProcessPush,
            new ProcessEvent($id, $process)
        );
    }

    private function processPull(string $id, Process $process): void
    {
        $process->stop(0);
        $this->trigger(
            SchedulerEvent::ProcessPull,
            new ProcessEvent($id, $process)
        );

        unset($this->processesActive[$id]);
        if ($this->processesActive === []) {
            $this->keeperDisable();
        }
    }
}
