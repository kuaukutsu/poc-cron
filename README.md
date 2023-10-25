# Scheduler (on event loop)

```php
<?php

declare(strict_types=1);

use kuaukutsu\poc\cron\SchedulerCommand;
use kuaukutsu\poc\cron\SchedulerCommandCollection;
use kuaukutsu\poc\cron\Scheduler;
use kuaukutsu\poc\cron\SchedulerOptions;
use kuaukutsu\poc\cron\SchedulerTimer;

require dirname(__DIR__) . '/vendor/autoload.php';

$scheduler = new Scheduler(
    new SchedulerCommandCollection(
        new SchedulerCommand(
            new PwdProcess(),
            SchedulerTimer::everyMinute()
        ),
        new SchedulerCommand(
            new SleepProcess(5),
            SchedulerTimer::everyMinute()
        ),
        new SchedulerCommand(
            new SleepProcess(10),
            SchedulerTimer::everyMinute()
        ),
        new SchedulerCommand(
            new SleepProcess(25),
            SchedulerTimer::everyMinute()
        ),
    ),
    new SchedulerOptions(
        tack: 30,
        keeperInterval: 5,
        timeout: 86400,
    )
);

$scheduler->run();
```

### Console Output

```php
<?php

declare(strict_types=1);

use kuaukutsu\poc\cron\Scheduler;
use kuaukutsu\poc\cron\tools\SchedulerOutput;

require dirname(__DIR__) . '/vendor/autoload.php';

$scheduler = new Scheduler(...);
$scheduler->on(new SchedulerOutput());
$scheduler->run();
```

## Docker

```shell
docker pull ghcr.io/kuaukutsu/php:8.1-cli
```

Container: 
- `ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli` (**default**)
- `jakzal/phpqa:php${PHP_VERSION}`

shell

```shell
docker run --init -it --rm -v "$(pwd):/app" -w /app ghcr.io/kuaukutsu/php:8.1-cli sh
```

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
make phpunit
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
make psalm
```

### Code Sniffer

```shell
make phpcs
```

### Rector

```shell
make rector
```
