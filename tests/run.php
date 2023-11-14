#!/usr/bin/env php
<?php

/**
 * Scheduler Example.
 */

declare(strict_types=1);

use kuaukutsu\poc\cron\tools\SchedulerOutput;
use kuaukutsu\poc\cron\tools\ProcessDecorator;
use kuaukutsu\poc\cron\Scheduler;
use kuaukutsu\poc\cron\SchedulerCommand;
use kuaukutsu\poc\cron\SchedulerOptions;
use kuaukutsu\poc\cron\SchedulerTimer;

require __DIR__ . '/bootstrap.php';

$scheduler = new Scheduler(
    new SchedulerCommand(
        new ProcessDecorator(['pwd']),
        SchedulerTimer::everyMinute()
    ),
    new SchedulerCommand(
        new ProcessDecorator([PHP_BINARY, dirname(__DIR__) . '/src/test.php']),
        SchedulerTimer::everyMinute()
    ),
    new SchedulerCommand(
        new ProcessDecorator(['sleep', '10'], 5.),
        SchedulerTimer::everyNMinutes(2)
    ),
);

$scheduler->on(new SchedulerOutput());
/** @noinspection PhpUnhandledExceptionInspection */
$scheduler->run(
    new SchedulerOptions(
        interval: 30,
        keeperInterval: 5,
        timeout: 300,
    )
);
