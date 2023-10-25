<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

use Closure;
use DateTimeImmutable;
use Revolt\EventLoop;
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

    private readonly string $runnerId;

    private readonly string $keeperId;

    /**
     * @throws EventLoop\UnsupportedFeatureException
     */
    public function __construct(
        SchedulerCommandCollection $collection,
        SchedulerOptions $options = new SchedulerOptions(),
    ) {
        $this->runnerId = EventLoop::repeat(
            $options->getRunnerInterval(),
            function () use ($collection): void {
                $tick = new DateTimeImmutable();
                $this->trigger(
                    SchedulerEvent::LoopTick,
                    new LoopTickEvent($tick)
                );

                foreach ($collection as $command) {
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
            }
        );

        $this->keeperId = EventLoop::repeat(
            $options->getKeeperInterval(),
            function (): void {
                if ($this->processesActive === []) {
                    $this->keeperDisable();
                    return;
                }

                foreach ($this->processesActive as $id => $process) {
                    if ($process->isRunning() === false) {
                        $this->processPull($id, $process);
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
                        continue;
                    }

                    $this->trigger(
                        SchedulerEvent::ProcessState,
                        new ProcessEvent($id, $process)
                    );
                }
            }
        );

        $this->keeperDisable();
        $this->onSignals($options->signalsInterrupt);

        if ($options->timeout > 0) {
            EventLoop::delay($options->timeout, function (): void {
                $this->trigger(
                    SchedulerEvent::LoopTimeout,
                    new LoopTimeoutEvent(new DateTimeImmutable())
                );

                $this->loopExit(SIGTERM);
            });
        }
    }

    public function run(): void
    {
        EventLoop::run();
    }

    /**
     * @param int[] $signals ext-pcntl
     * @throws EventLoop\UnsupportedFeatureException
     */
    private function onSignals(array $signals): void
    {
        foreach ($signals as $signal) {
            EventLoop::onSignal($signal, function () use ($signal): void {
                $this->loopExit($signal);
            });
        }
    }

    private function keeperDisable(): void
    {
        EventLoop::disable($this->keeperId);
    }

    private function keeperEnable(): void
    {
        EventLoop::enable($this->keeperId);
    }

    private function loopExit(int $signal): void
    {
        foreach ($this->processesActive as $id => $process) {
            if ($process->isRunning()) {
                try {
                    $this->trigger(
                        SchedulerEvent::ProcessStop,
                        new ProcessEvent($id, $process)
                    );

                    $process->signal($signal);
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
        unset($this->processesActive[$id]);
        $this->trigger(
            SchedulerEvent::ProcessPull,
            new ProcessEvent($id, $process)
        );

        if ($this->processesActive === []) {
            $this->keeperDisable();
        }
    }
}
