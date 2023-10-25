<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron;

trait SchedulerPublisherEvent
{
    /**
     * @var array<class-string<SchedulerEvent>, array<int, callable(SchedulerEvent $name, EventInterface $event):void>>
     */
    private array $eventHandlers = [];

    final public function on(EventSubscriberInterface $subscriber): void
    {
        $subscriberHash = spl_object_id($subscriber);
        foreach ($subscriber->subscriptions() as $name => $callback) {
            $this->eventHandlers[$name][$subscriberHash] = $callback;
        }
    }

    final public function off(EventSubscriberInterface $subscriber): void
    {
        $subscriberHash = spl_object_id($subscriber);
        foreach (SchedulerEvent::cases() as $event) {
            if (array_key_exists($event->value, $this->eventHandlers)) {
                unset($this->eventHandlers[$event->value][$subscriberHash]);
            }
        }
    }

    private function trigger(SchedulerEvent $name, EventInterface $event): void
    {
        if (array_key_exists($name->value, $this->eventHandlers)) {
            foreach ($this->eventHandlers[$name->value] as $subscriberCallback) {
                $subscriberCallback($name, $event);
            }
        }
    }
}
