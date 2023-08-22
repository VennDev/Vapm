<?php

/**
 * Vapm - A library support for PHP about Async, Promise, Coroutine, Thread, GreenThread
 *          and other non-blocking methods. The library also includes some Javascript packages
 *          such as Express. The method is based on Fibers & Generator & Processes, requires
 *          you to have php version from >= 8.1
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

declare(strict_types=1);

namespace vennv\vapm\simultaneous;

use Throwable;
use vennv\vapm\simultaneous\enums\StatusPromise;
use vennv\api\simultaneous\EventLoopInterface;

use function count;
use const PHP_INT_MAX;

class EventLoop implements EventLoopInterface
{
    protected static int $nextId = 0;

    /**
     * @var array<int, Promise>
     */
    protected static array $queues = [];

    /**
     * @var array<int, Promise>
     */
    protected static array $returns = [];

    public static function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX) {
            self::$nextId = 0;
        }

        return self::$nextId++;
    }

    public static function addQueue(Promise $promise): void
    {
        $id = $promise->getId();

        if (isset(self::$queues[$id])) return;

        self::$queues[$id] = $promise;
    }

    public static function getQueue(int $id): ?Promise
    {
        return self::$queues[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getQueues(): array
    {
        return self::$queues;
    }

    public static function addReturn(Promise $promise): void
    {
        $id = $promise->getId();

        if (isset(self::$returns[$id])) return;

        self::$returns[$id] = $promise;
    }

    public static function getReturn(int $id): ?Promise
    {
        return self::$returns[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getReturns(): array
    {
        return self::$returns;
    }

    /**
     * @throws Throwable
     */
    protected static function runSingle(): void
    {
        while (count(self::$queues) > 0 || count(MicroTask::getTasks()) > 0 || count(MacroTask::getTasks()) > 0 || count(GreenThread::getFibers()) > 0) {
            self::run();
        }
    }

    /**
     * @throws Throwable
     */
    protected static function run(): void
    {
        if (count(GreenThread::getFibers()) > 0) {
            GreenThread::run();
        }

        foreach (self::$queues as $id => $promise) {
            $fiber = $promise->getFiber();

            if ($fiber->isSuspended()) {
                $fiber->resume();
            } else if (!$fiber->isTerminated()) {
                FiberManager::wait();
            }

            if ($fiber->isTerminated() && ($promise->getStatus() !== StatusPromise::PENDING || $promise->isJustGetResult())) {
                MicroTask::addTask($id, $promise);
                self::removeQueue($id);
            }
        }

        if (count(MicroTask::getTasks()) > 0) {
            MicroTask::run();
        }

        if (count(MacroTask::getTasks()) > 0) {
            MacroTask::run();
        }

        self::clearGarbage();
    }

    public static function removeQueue(int $id): void
    {
        unset(self::$queues[$id]);
    }

    private static function clearGarbage(): void
    {
        foreach (self::$returns as $id => $promise) {
            if (!$promise->canDrop()) continue;

            self::removeReturn($id);
        }
    }

    public static function removeReturn(int $id): void
    {
        unset(self::$returns[$id]);
    }
}
