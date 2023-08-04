<?php

/**
 * Vapm - A library for PHP about Async, Promise, Coroutine, GreenThread,
 *      Thread and other non-blocking methods. The method is based on Fibers &
 *      Generator & Processes, requires you to have php version from >= 8.1
 *
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use ReflectionException;
use SplQueue;
use Generator;
use Throwable;
use function is_callable;
use function call_user_func;

interface CoroutineScopeInterface {

    /**
     * @return bool
     *
     * This function checks if the coroutine has finished.
     */
    public static function isFinished() : bool;

    /**
     * @return bool
     *
     * This function checks if the coroutine has been cancelled.
     */
    public static function isCancelled() : bool;

    /**
     * This function cancels the coroutine.
     */
    public static function cancel() : void;

    /**
     * @param ChildCoroutine|CoroutineScope $childCoroutine
     * @throws ReflectionException
     * @throws Throwable
     *
     * This function launches a coroutine.
     */
    public static function launch(ChildCoroutine|CoroutineScope $childCoroutine) : void;

    /**
     * This function runs the coroutine.
     */
    public function run() : void;

}

final class CoroutineScope implements CoroutineScopeInterface {

    protected static ?SplQueue $taskQueue = null;

    protected static bool $cancelled = false;

    protected static bool $finished = false;

    protected static string $dispatcher;

    public function __construct(string $dispatcher = Dispatchers::DEFAULT) {
        self::$dispatcher = $dispatcher;
    }

    public static function isFinished() : bool {
        return self::$finished;
    }

    public static function isCancelled() : bool {
        return self::$cancelled;
    }

    public static function cancel() : void {
        self::$cancelled = true;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function launch(mixed ...$callbacks) : void {
        foreach ($callbacks as $callback) {
            if ($callback instanceof CoroutineScope) {
                self::schedule($callback);
            } else if (is_callable($callback)) {
                if (self::$dispatcher === Dispatchers::IO) {
                    $class = (new class extends Thread {

                        private mixed $callback;

                        public function __construct(callable $callback) {
                            $this->callback = $callback;
                            parent::__construct('');
                        }

                        public function onRun() : void {
                            call_user_func($this->callback);
                        }

                    })($callback);

                    $class->start();
                }

                if (self::$dispatcher === Dispatchers::DEFAULT) {
                    $callback = call_user_func($callback);
                }
            } else {
                $callback = fn() => $callback;
            }

            if ($callback instanceof Generator) {
                self::schedule(new ChildCoroutine($callback));
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function run() : void {
        if (self::$taskQueue !== null && !self::$taskQueue->isEmpty() && self::$cancelled === false) {
            $coroutine = self::$taskQueue->dequeue();

            if ($coroutine instanceof ChildCoroutine) {
                $coroutine->run();

                if (!$coroutine->isFinished()) {
                    self::schedule($coroutine);
                }
            }

            if ($coroutine instanceof CoroutineScope) {
                $coroutine->run();

                if (!$coroutine->isFinished()) {
                    self::schedule($coroutine);
                }
            }
        } else {
            self::$finished = true;
        }
    }

    private static function schedule(ChildCoroutine|CoroutineScope $childCoroutine) : void {
        if (self::$taskQueue === null) {
            self::$taskQueue = new SplQueue();
        }

        self::$taskQueue->enqueue($childCoroutine);
    }

}