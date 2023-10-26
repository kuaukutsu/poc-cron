# Scheduler (on event loop)

### Get started

```php
<?php

declare(strict_types=1);

use kuaukutsu\poc\cron\tools\ProcessDecorator;
use kuaukutsu\poc\cron\SchedulerCommand;
use kuaukutsu\poc\cron\Scheduler;
use kuaukutsu\poc\cron\SchedulerTimer;

require dirname(__DIR__) . '/vendor/autoload.php';

$scheduler = new Scheduler(
    new SchedulerCommand(
        new ProcessDecorator(['pwd']),
        SchedulerTimer::everyMinute()
    ),
    new SchedulerCommand(
        new ProcessDecorator(['sleep', '10']),
        SchedulerTimer::everyFiveMinutes()
    ),
);

$scheduler->run();
```

### Custom Options

```php
<?php

declare(strict_types=1);

use kuaukutsu\poc\cron\Scheduler;
use kuaukutsu\poc\cron\SchedulerOptions;

require dirname(__DIR__) . '/vendor/autoload.php';

$scheduler = new Scheduler(...);
$scheduler->run(
    new SchedulerOptions(
        interval: 30,
        keeperInterval: 5,
        timeout: 86400,
    )
);
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
