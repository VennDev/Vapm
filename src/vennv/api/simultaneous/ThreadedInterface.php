<?php

namespace vennv\api\simultaneous;

interface ThreadedInterface
{
    /**
     * @return array<string, mixed>
     * @phpstan-return array<string, mixed>
     *
     * This method use to get the shared data of the main thread
     */
    public static function getDataMainThread(): array;

    /**
     * @param array<string, mixed> $shared
     *
     * @phpstan-param array<string, mixed> $shared
     *
     * This method use to set the shared data of the main thread
     */
    public static function setShared(array $shared): void;

    /**
     * @param string $key
     * @param mixed $value
     *
     * @phpstan-param mixed $value
     *
     * This method use to add the shared data of the MAIN-THREAD
     */
    public static function addShared(string $key, mixed $value): void;

    /*
     * This method use to get the running status of the thread
     */

    /**
     * @return array<string, mixed>
     *
     * This method use to get the shared data of the child thread
     */
    public static function getSharedData(): array;

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     * @phpstan-param array<string, mixed> $data
     *
     * This method use to post all data the main thread
     */
    public static function postMainThread(array $data): void;

    /**
     * @param string $data
     *
     * @return void
     *
     * This method use to load the shared data from the main thread
     */
    public static function loadSharedData(string $data): void;

    /**
     * @param string $data
     *
     * @return void
     *
     * This method use to alert for the main thread
     */
    public static function alert(string $data): void;

    /**
     * @param int $pid
     *
     * @return bool
     *
     * This method use to check the thread is running or not
     */
    public static function threadIsRunning(int $pid): bool;

    /**
     * @param int $pid
     *
     * @return bool
     *
     * This method use to kill the thread
     */
    public static function killThread(int $pid): bool;

    /**
     * @return mixed
     */
    public function getInput(): mixed;

    /**
     * This method use to get the pid of the thread
     */
    public function getPid(): int;

    /**
     * This method use to get the exit code of the thread
     */
    public function getExitCode(): int;

    public function isRunning(): bool;

    /**
     * This method use to get the signaled status of the thread
     */
    public function isSignaled(): bool;

    /**
     * This method use to get the stopped status of the thread
     */
    public function isStopped(): bool;
}
