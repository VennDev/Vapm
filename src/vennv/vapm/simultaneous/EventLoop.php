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

declare(strict_types = 1);

namespace vennv\vapm\simultaneous;

use ArrayObject;
use Throwable;
use function count;
use const PHP_INT_MAX;

interface EventLoopInterface {

    public static function init() : void;

    public static function generateId() : int;

    public static function addQueue(Promise $promise) : void;

    public static function removeQueue(int $id) : void;

    public static function isQueue(int $id) : bool;

    public static function getQueue(int $id) : ?Promise;

    /**
     * @return ArrayObject
     */
    public static function getQueues() : ArrayObject;

    public static function addReturn(Promise $promise) : void;

    public static function removeReturn(int $id) : void;

    public static function isReturn(int $id) : bool;

    public static function getReturn(int $id) : ?Promise;

    /**
     * @return ArrayObject
     */
    public static function getReturns() : ArrayObject;

}

class EventLoop implements EventLoopInterface {

    protected static int $nextId = 0;

    /**
     * @var ArrayObject
     */
    protected static ArrayObject $queues;

    /**
     * @var ArrayObject
     */
    protected static ArrayObject $returns;

    public static function init() : void {
        if (!isset(self::$queues)) self::$queues = new ArrayObject();
        if (!isset(self::$returns)) self::$returns = new ArrayObject();
    }

    public static function generateId() : int {
        if (self::$nextId >= PHP_INT_MAX) {
            self::$nextId = 0;
        }

        return self::$nextId++;
    }

    public static function addQueue(Promise $promise) : void {
        if (!self::getQueue($id = $promise->getId())) self::$queues->offsetSet($id, $promise);
    }

    public static function removeQueue(int $id) : void {
        self::$queues->offsetUnset($id);
    }

    public static function isQueue(int $id) : bool {
        return self::$queues->offsetExists($id);
    }

    public static function getQueue(int $id) : ?Promise {
        return self::isQueue($id) ? self::$queues->offsetGet($id) : null; /* @phpstan-ignore-line */
    }

    /**
     * @return ArrayObject
     */
    public static function getQueues() : ArrayObject {
        return self::$queues;
    }

    public static function addReturn(Promise $promise) : void {
        if (!self::getReturn($id = $promise->getId())) self::$returns->offsetSet($id, $promise);
    }

    public static function isReturn(int $id) : bool {
        return self::$returns->offsetExists($id);
    }

    public static function removeReturn(int $id) : void {
        self::$returns->offsetUnset($id);
    }

    public static function getReturn(int $id) : ?Promise {
        return self::$returns->offsetExists($id) ? self::$returns->offsetGet($id) : null; /* @phpstan-ignore-line */
    }

    /**
     * @return ArrayObject
     */
    public static function getReturns() : ArrayObject {
        return self::$returns;
    }

    private static function clearGarbage() : void {
        /**
         * @var int $id
         * @var Promise $promise
         */
        foreach (self::$returns->getIterator() as $id => $promise) {
            if ($promise->canDrop()) {
                self::removeReturn($id);
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected static function run() : void {
        if (count(GreenThread::getFibers()) > 0) {
            GreenThread::run();
        }

        /**
         * @var int $id
         * @var Promise $promise
         */
        foreach (self::$queues->getIterator() as $id => $promise) {
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

    /**
     * @throws Throwable
     */
    protected static function runSingle() : void {
        while (count(self::$queues) > 0 || count(MicroTask::getTasks()) > 0 || count(MacroTask::getTasks()) > 0 || count(GreenThread::getFibers()) > 0) {
            self::run();
        }
    }

}