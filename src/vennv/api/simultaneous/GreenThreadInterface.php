<?php

namespace vennv\api\simultaneous;

use Fiber;
use Throwable;
use vennv\vapm\simultaneous\StatusThread;

interface GreenThreadInterface
{

    /**
     * @param string|int $name
     * @param callable $callback
     * @param array<int, mixed> $params
     *
     * @throws Throwable
     *
     * This method is used to register a green thread.
     */
    public static function register(string|int $name, callable $callback, array $params): void;

    /**
     * @throws Throwable
     */
    public static function run(): void;

    /**
     * This method is used to clear the data of the green threads.
     */
    public static function clear(): void;

    /**
     * @return array<int, string|int>
     *
     * This method is used to get the names of the green threads.
     */
    public static function getNames(): array;

    /**
     * @return array<int, Fiber>
     *
     * This method is used to get the fibers of the green threads.
     */
    public static function getFibers(): array;

    /**
     * @return array<int, array<int, mixed>>
     *
     * This method is used to get the params of the green threads.
     */
    public static function getParams(): array;

    /**
     * @return array<string|int, mixed>
     *
     * This method is used to get the outputs of the green threads.
     */
    public static function getOutputs(): array;

    /**
     * @param string|int $name
     *
     * @return mixed
     *
     * This method is used to get the output of a green thread.
     */
    public static function getOutput(string|int $name): mixed;

    /**
     * @throws Throwable
     */
    public static function sleep(string $name, int $seconds): void;

    /**
     * @param string|int $name
     *
     * @return StatusThread|null
     *
     * This method is used to get the status of a green thread.
     */
    public static function getStatus(string|int $name): StatusThread|null;
}
