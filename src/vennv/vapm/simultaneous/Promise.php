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

use Fiber;
use Throwable;
use vennv\vapm\System;
use vennv\api\simultaneous\PromiseInterface;
use vennv\vapm\simultaneous\enums\StatusPromise;

use function count;
use function microtime;
use function is_callable;
use function call_user_func;

final class Promise implements PromiseInterface
{
    private int $id;

    private float $timeOut = 0.0;

    private float $timeEnd = 0.0;

    private mixed $result = null;

    private mixed $return = null;

    private StatusPromise $status = StatusPromise::PENDING;

    /** @var array<int|string, callable> $callbacksResolve */
    private array $callbacksResolve = [];

    /** @var callable $callbacksReject */
    private mixed $callbackReject;

    /** @var callable $callbackFinally */
    private mixed $callbackFinally;

    private float $timeStart;

    private Fiber $fiber;

    /** @var callable $callback */
    private mixed $callback;

    private bool $justGetResult;

    /**
     * @param callable $callback
     * @param bool $justGetResult
     *
     * @throws Throwable
     */
    public function __construct(callable $callback, bool $justGetResult = false)
    {
        System::init();

        $this->id = EventLoop::generateId();

        $this->callback = $callback;
        $this->fiber = new Fiber($callback);

        if ($justGetResult) {
            $this->result = $this->fiber->start();
        } else {
            $resolve = function ($result = ''): void {
                $this->resolve($result);
            };

            $reject = function ($result = ''): void {
                $this->reject($result);
            };

            $this->fiber->start($resolve, $reject);
        }

        if (!$this->fiber->isTerminated()) {
            FiberManager::wait();
        }

        $this->justGetResult = $justGetResult;

        $this->timeStart = microtime(true);

        $this->callbacksResolve["master"] = function ($result): mixed {
            return $result;
        };

        $this->callbackReject = function ($result): mixed {
            return $result;
        };

        $this->callbackFinally = function (): void {
        };

        EventLoop::addQueue($this);
    }

    public function resolve(mixed $value = ''): void
    {
        if (!$this->isPending()) return;

        $this->status = StatusPromise::FULFILLED;
        $this->result = $value;
    }

    public function isPending(): bool
    {
        return $this->status === StatusPromise::PENDING;
    }

    public function reject(mixed $value = ''): void
    {
        if (!$this->isPending()) return;

        $this->status = StatusPromise::REJECTED;
        $this->result = $value;
    }

    /**
     * @throws Throwable
     */
    public static function c(callable $callback, bool $justGetResult = false): Promise
    {
        return new self($callback, $justGetResult);
    }

    /**
     * @param array<int, Async|Promise|callable> $promises
     *
     * @phpstan-param array<int, Async|Promise|callable> $promises
     * @throws Throwable
     */
    public static function all(array $promises): Promise
    {
        $promise = new Promise(function ($resolve, $reject) use ($promises): void {
            $results = [];
            $isSolved = false;

            while ($isSolved === false) {
                foreach ($promises as $promise) {
                    if (is_callable($promise)) {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise) {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return?->isRejected()) {
                            $reject($return->getResult());
                            $isSolved = true;
                        }

                        if ($return?->isResolved()) {
                            $results[] = $return->getResult();
                        }
                    }

                    if (count($results) === count($promises)) {
                        $resolve($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false) {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    public function getReturn(): mixed
    {
        return $this->return;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isRejected(): bool
    {
        return $this->status === StatusPromise::REJECTED;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): Promise
    {
        $this->result = $result;
        return $this;
    }

    public function isResolved(): bool
    {
        return $this->status === StatusPromise::FULFILLED;
    }

    /**
     * @param array<int, Async|Promise|callable> $promises
     *
     * @phpstan-param array<int, Async|Promise|callable> $promises
     * @throws Throwable
     */
    public static function allSettled(array $promises): Promise
    {
        $promise = new Promise(function ($resolve) use ($promises): void {
            $results = [];
            $isSolved = false;

            while ($isSolved === false) {
                foreach ($promises as $promise) {
                    if (is_callable($promise)) {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise) {
                        $return = EventLoop::getReturn($promise->getId());

                        if (!is_null($return)) {
                            $results[] = new PromiseResult($return->getStatus(), $return->getResult());
                        }
                    }

                    if (count($results) === count($promises)) {
                        $resolve($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false) {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    public function getStatus(): StatusPromise
    {
        return $this->status;
    }

    /**
     * @param array<int, Async|Promise|callable> $promises
     *
     * @phpstan-param array<int, Async|Promise|callable> $promises
     * @throws Throwable
     */
    public static function any(array $promises): Promise
    {
        $promise = new Promise(function ($resolve, $reject) use ($promises): void {
            $results = [];
            $isSolved = false;

            while ($isSolved === false) {
                foreach ($promises as $promise) {
                    if (is_callable($promise)) {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise) {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return?->isRejected()) {
                            $results[] = $return->getResult();
                        }

                        if ($return?->isResolved()) {
                            $resolve($return->getResult());
                            $isSolved = true;
                        }
                    }

                    if (count($results) === count($promises)) {
                        $reject($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false) {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    /**
     * @param array<int, Async|Promise|callable> $promises
     *
     * @phpstan-param array<int, Async|Promise|callable> $promises
     * @throws Throwable
     */
    public static function race(array $promises): Promise
    {
        $promise = new Promise(function ($resolve, $reject) use ($promises): void {
            $isSolved = false;

            while ($isSolved === false) {
                foreach ($promises as $promise) {
                    if (is_callable($promise)) {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise) {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return?->isRejected()) {
                            $reject($return->getResult());
                            $isSolved = true;
                        }

                        if ($return?->isResolved()) {
                            $resolve($return->getResult());
                            $isSolved = true;
                        }
                    }
                }

                if ($isSolved === false) {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    public function getFiber(): Fiber
    {
        return $this->fiber;
    }

    public function isJustGetResult(): bool
    {
        return $this->justGetResult;
    }

    public function getTimeOut(): float
    {
        return $this->timeOut;
    }

    public function getTimeStart(): float
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): float
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(float $timeEnd): void
    {
        $this->timeEnd = $timeEnd;
    }

    public function canDrop(): bool
    {
        return microtime(true) - $this->timeEnd > Settings::TIME_DROP;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @throws Throwable
     */
    public function useCallbacks(): void
    {
        $result = $this->result;

        if ($this->isRejected() && is_callable($this->callbackReject)) {
            $this->result = call_user_func($this->callbackReject, $result);

            if (is_callable($this->callbackFinally)) {
                call_user_func($this->callbackFinally);
            }

            return;
        }

        $callbacks = $this->callbacksResolve;
        $master = $callbacks["master"];

        $this->result = call_user_func($master, $result);

        unset($callbacks["master"]);

        if (count($callbacks) <= 0) return;

        $resultFirstCallback = call_user_func($callbacks[0], $this->result);

        $this->result = $resultFirstCallback;
        $this->return = $resultFirstCallback;
        $this->checkStatus($callbacks, $this->return);
    }

    /**
     * @param array<callable> $callbacks
     *
     * @phpstan-param array<callable> $callbacks
     * @throws Throwable
     */
    private function checkStatus(array $callbacks, mixed $return): void
    {
        $lastPromise = null;

        while (count($callbacks) > 0) {
            $cancel = false;

            foreach ($callbacks as $case => $callable) {
                if (is_null($return)) {
                    $cancel = true;
                    break;
                }

                if ($case !== 0 && $return instanceof Promise) {
                    EventLoop::addQueue($return);

                    $queue1 = EventLoop::getQueue($return->getId());
                    $queue2 = MicroTask::getTask($return->getId());

                    if (!is_null($queue1)) {
                        $queue1->then($callable);

                        if (is_callable($this->callbackReject)) {
                            $queue1->catch($this->callbackReject);
                        }

                        $lastPromise = $queue1;
                    } else if (!is_null($queue2)) {
                        $queue2->then($callable);

                        if (is_callable($this->callbackReject)) {
                            $queue2->catch($this->callbackReject);
                        }

                        $lastPromise = $queue2;
                    }

                    unset($callbacks[$case]);
                    continue;
                }

                if (count($callbacks) === 1) {
                    $cancel = true;
                }
            }

            if ($cancel) break;
        }

        if (!is_null($lastPromise)) {
            $lastPromise->finally($this->callbackFinally);
            return;
        }

        if (is_callable($this->callbackFinally)) {
            call_user_func($this->callbackFinally);
        }
    }

    public function then(callable $callback): Promise
    {
        $this->callbacksResolve[] = $callback;

        return $this;
    }

    public function catch(callable $callback): Promise
    {
        $this->callbackReject = $callback;

        return $this;
    }

    public function finally(callable $callback): Promise
    {
        $this->callbackFinally = $callback;

        return $this;
    }
}
