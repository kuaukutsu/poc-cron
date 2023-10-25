<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tools;

use kuaukutsu\poc\cron\SchedulerEvent;
use kuaukutsu\poc\cron\event\ProcessEvent;
use kuaukutsu\poc\cron\EventInterface;
use kuaukutsu\poc\cron\EventSubscriberInterface;

final class SchedulerOutput implements EventSubscriberInterface
{
    public function subscriptions(): array
    {
        $subscriptions = [];
        foreach (SchedulerEvent::cases() as $event) {
            $subscriptions[$event->value] = $this->trace(...);
        }

        $subscriptions[SchedulerEvent::ProcessState->value] = $this->traceProcessState(...);

        /**
         * @var array<class-string<SchedulerEvent>, callable(SchedulerEvent $name, EventInterface $event):void>
         */
        return $subscriptions;
    }

    public function trace(SchedulerEvent $name, EventInterface $event): void
    {
        match ($name) {
            SchedulerEvent::ProcessPush => $this->stdout('push: ' . $event->getMessage()),
            SchedulerEvent::ProcessPull => $this->stdout('pull: ' . $event->getMessage()),
            SchedulerEvent::ProcessStop => $this->stdout('stop: ' . $event->getMessage()),
            SchedulerEvent::ProcessExists => $this->stdout('exists: ' . $event->getMessage()),
            SchedulerEvent::ProcessTimeout => $this->stdout('timeout: ' . $event->getMessage()),
            default => $this->stdout($event->getMessage())
        };
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function traceProcessState(SchedulerEvent $name, ProcessEvent $event): void
    {
        $this->stdout($event->getStatus() . ': ' . $event->getMessage());
    }

    private function stdout(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}
