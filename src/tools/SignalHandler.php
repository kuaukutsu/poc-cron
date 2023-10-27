<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tools;

use Closure;
use RuntimeException;
use SplQueue;

/**
 * @example
 * ```php
 *  $this->signalHandler->onSignal(
 *      [SIGHUP, SIGINT, SIGTERM],
 *      function (int $signal) {
 *          $this->stdout('Вызван обработчик сигнала ' . $signal . PHP_EOL);
 *      }
 *  );
 *  // code
 *  $this->signalHandler->dispatch();
 * ```
 *
 * @see \Revolt\EventLoop\Driver\StreamSelectDriver
 */
final class SignalHandler
{
    /**
     * @var array<int, array<string, SignalCallback>>
     */
    private array $signalCallbacks = [];

    /**
     * @var string Next callback identifier.
     */
    private string $nextId = 'a';

    private readonly bool $signalHandling;

    /**
     * @var SplQueue<int>
     */
    private readonly SplQueue $signalQueue;

    public function __construct()
    {
        $this->signalQueue = new SplQueue();
        $this->signalHandling = extension_loaded("pcntl")
            && function_exists('pcntl_signal_dispatch')
            && function_exists('pcntl_signal');
    }

    public function __destruct()
    {
        foreach ($this->signalCallbacks as $signalCallbacks) {
            foreach ($signalCallbacks as $signalCallback) {
                $this->deactivate($signalCallback);
            }
        }
    }

    /**
     * @param int[] $signals
     */
    public function onSignal(array $signals, Closure $callback): void
    {
        foreach ($signals as $signal) {
            $this->activate(
                new SignalCallback($this->nextId++, $signal, $callback)
            );
        }
    }

    public function dispatch(): void
    {
        if ($this->signalHandling) {
            pcntl_signal_dispatch();

            while ($this->signalQueue->isEmpty() === false) {
                $signal = $this->signalQueue->dequeue();

                foreach ($this->signalCallbacks[$signal] as $callback) {
                    $callback->call();
                }
            }
        }
    }

    /**
     * @throws RuntimeException
     */
    private function activate(SignalCallback $callback): void
    {
        if (isset($this->signalCallbacks[$callback->signal]) === false) {
            set_error_handler(static function (int $errno, string $errstr): bool {
                throw new RuntimeException(
                    sprintf("Failed to register signal handler; Errno: %d; %s", $errno, $errstr)
                );
            });

            try {
                pcntl_signal($callback->signal, $this->handleSignal(...));
            } finally {
                restore_error_handler();
            }
        }

        $this->signalCallbacks[$callback->signal][$callback->id] = $callback;
    }

    private function deactivate(SignalCallback $callback): void
    {
        if (isset($this->signalCallbacks[$callback->signal])) {
            unset($this->signalCallbacks[$callback->signal][$callback->id]);

            if (empty($this->signalCallbacks[$callback->signal])) {
                unset($this->signalCallbacks[$callback->signal]);
                set_error_handler(static fn() => true);
                try {
                    pcntl_signal($callback->signal, SIG_DFL);
                } finally {
                    restore_error_handler();
                }
            }
        }
    }

    private function handleSignal(int $signal): void
    {
        // Queue signals, so we don't suspend inside pcntl_signal_dispatch, which disables signals while it runs
        $this->signalQueue->enqueue($signal);
    }
}
