<?php

namespace vennv\api\simultaneous;

use Closure;
use Generator;
use vennv\vapm\simultaneous\CoroutineScope;

interface CoroutineGenInterface
{
    /**
     * @param mixed ...$coroutines
     *
     * @return void
     *
     * This is a blocking function that runs all the coroutines passed to it.
     */
    public static function runBlocking(mixed ...$coroutines): void;

    /**
     * @param callable $callback
     * @param int $times
     *
     * @return Closure
     *
     * This is a generator that runs a callback function a specified amount of times.
     */
    public static function repeat(callable $callback, int $times): Closure;

    /**
     * @param int $milliseconds
     *
     * @return Generator
     *
     * This is a generator that yields for a specified amount of milliseconds.
     */
    public static function delay(int $milliseconds): Generator;

    /**
     * @param mixed ...$callback
     *
     * @return CoroutineScope
     *
     * This is a generator that runs a callback function.
     */
    public static function launch(mixed ...$callback): CoroutineScope;

}