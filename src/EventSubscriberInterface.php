<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

interface EventSubscriberInterface
{
    /**
     * @return array<class-string<SchedulerEvent>, callable(SchedulerEvent $name, EventInterface $event):void>
     */
    public function subscriptions(): array;
}
