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

use Exception;
use Throwable;

interface WorkerInterface
{

    /**
     * @return Work
     *
     * Get the work.
     */
    public function getWork(): Work;

    /**
     * @return void
     *
     * This is method help you to remove the worker from the worker list.
     * You should call this method when the work is done to avoid memory leaks.
     */
    public function done(): void;

    /**
     * @param mixed $result
     * @return void
     *
     * Collect the result of the work.
     */
    public function collect(mixed $result): void;

    /**
     * @return array<int, mixed>
     *
     * Get the result of the work.
     */
    public function get(): array;

    /**
     * @throws Throwable
     * @return Async
     *
     * Run the work.
     */
    public function run(callable $callback): Async;

}

final class Worker implements WorkerInterface
{

    protected static int $nextId = 0;

    public int $id;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @var array<int, array<int, mixed>>
     */
    private static array $workers = [];

    private Work $work;

    /**
     * @param Work $work
     * @param array<string, mixed> $options
     */
    public function __construct(Work $work, array $options = [
        "threads" => 4,
        "max_queue" => 16
    ])
    {
        $this->work = $work;
        $this->options = $options;
        $this->id = $this->generateId();

        self::$workers[$this->id] = [];
    }

    private function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX) self::$nextId = 0;
        return self::$nextId++;
    }

    public function getWork(): Work
    {
        return $this->work;
    }

    public function done(): void
    {
        unset(self::$workers[$this->id]);
    }

    public function collect(mixed $result): void
    {
        self::$workers[$this->id][] = $result;
    }

    /**
     * @return array<int, mixed>
     */
    public function get(): array
    {
        return self::$workers[$this->id];
    }

    /**
     * @throws Throwable
     */
    public function run(callable $callback): Async
    {
        $work = $this->getWork();

        return new Async(function () use ($work, $callback): void {
            $threads = $this->options["threads"];
            $max_queue = $this->options["max_queue"];

            if ($threads >= 1) {
                if ($work->count() > $max_queue) throw new Exception(Error::QUEUE_IS_FULL);

                $i = 0;
                $promises = [];
                while ($work->count() > 0) {
                    $callbackQueue = $work->dequeue();

                    if (!is_callable($callbackQueue)) return;

                    $thread = new CoroutineThread($callbackQueue);
                    $promises[] = $thread->start();

                    if (++$i <= $threads) {
                        $data = Async::await(Promise::all($promises));
                        $this->collect($data);
                        $i = 0;
                        $promises = [];
                    }
                }

                $data = Async::await(Stream::flattenArray($this->get()));

                call_user_func($callback, $data, $this);
            }
        });
    }

}