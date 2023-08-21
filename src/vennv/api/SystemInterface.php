<?php

namespace vennv\api;

use Throwable;
use vennv\vapm\simultaneous\Promise;
use vennv\vapm\simultaneous\SampleMacro;

interface SystemInterface
{
    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with multiple event loops
     */
    public static function runEventLoop(): void;

    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with single event loop
     */
    public static function runSingleEventLoop(): void;

    /**
     * @throws Throwable
     *
     * This function is used to initialize the event loop
     */
    public static function init(): void;

    /**
     * This function is used to run a callback in the event loop with timeout
     */
    public static function setTimeout(callable $callback, int $timeout): SampleMacro;

    /**
     * This function is used to clear the timeout
     */
    public static function clearTimeout(SampleMacro $sampleMacro): void;

    /**
     * This function is used to run a callback in the event loop with interval
     */
    public static function setInterval(callable $callback, int $interval): SampleMacro;

    /**
     * This function is used to clear the interval
     */
    public static function clearInterval(SampleMacro $sampleMacro): void;

    /**
     * @param string $url
     * @param array<string|null, string|array> $options
     *
     * @return Promise when Promise resolve InternetRequestResult and when Promise reject Error
     * @throws Throwable
     * @phpstan-param array{method?: string, headers?: array<int, string>, timeout?: int, body?: array<string, string>} $options
     */
    public static function fetch(string $url, array $options = []): Promise;

    /**
     * @param string ...$curls
     *
     * @return Promise
     * @throws Throwable
     *
     * Use this to curl multiple addresses at once
     */
    public static function fetchAll(string ...$curls): Promise;

    /**
     * @throws Throwable
     *
     * This is a function used only to retrieve results from an address or file path via the file_get_contents method
     */
    public static function read(string $path): Promise;

    /**
     * @param string $name
     *
     * @return void
     *
     * This function is used to start a timer
     */
    public static function time(string $name = 'Console'): void;

    /**
     * @param string $name
     *
     * @return void
     *
     * This function is used to end a timer
     */
    public static function timeEnd(string $name = 'Console'): void;
}