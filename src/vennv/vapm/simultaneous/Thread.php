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

use Closure;
use Throwable;
use ReflectionClass;
use RuntimeException;
use ReflectionException;
use vennv\vapm\utils\Utils;
use vennv\vapm\simultaneous\enums\ErrorMessage;
use vennv\api\simultaneous\ThreadInterface;
use vennv\api\simultaneous\ThreadedInterface;

use function fclose;
use function fwrite;
use function explode;
use function is_array;
use function is_string;
use function proc_open;
use function is_callable;
use function is_resource;
use function json_decode;
use function json_encode;
use function str_replace;
use function proc_get_status;
use function get_called_class;
use function stream_get_contents;
use function stream_set_blocking;

use const STDIN;
use const STDOUT;
use const PHP_EOL;
use const PHP_BINARY;

abstract class Thread implements ThreadInterface, ThreadedInterface
{
    private const POST_MAIN_THREAD = 'postMainThread'; // example: postMainThread=>{data}

    private const POST_ALERT_THREAD = 'postAlertThread'; // example: postAlertThread=>{data}

    /**
     * @var array<string, mixed>
     * @phpstan-var array<string, mixed>
     */
    private static array $shared = [];

    /**
     * @var array<int, Thread>
     * @phpstan-var array<int, Thread>
     */
    private static array $threads = [];

    /**
     * @var array<string, mixed>
     * @phpstan-var array<string, mixed>
     */
    private static array $inputs = [];

    private int $pid = -1;

    private int $exitCode = -1;

    private bool $isRunning = false;

    private bool $signaled = false;

    private bool $stopped = false;

    public function __construct(mixed $input = '')
    {
        self::$inputs[get_called_class()] = $input;
    }

    public static function addShared(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function getSharedData(): array
    {
        $data = fgets(STDIN);

        if (!is_string($data)) return [];

        $data = json_decode($data, true);

        if (is_array($data)) return $data;

        return [];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public static function postMainThread(array $data): void
    {
        fwrite(STDOUT, self::POST_MAIN_THREAD . '=>' . json_encode($data) . PHP_EOL);
    }

    public static function alert(string $data): void
    {
        fwrite(STDOUT, self::POST_ALERT_THREAD . '=>' . $data . PHP_EOL);
    }

    public static function threadIsRunning(int $pid): bool
    {
        return isset(self::$threads[$pid]);
    }

    public static function killThread(int $pid): bool
    {
        if (!isset(self::$threads[$pid])) return false;

        $thread = self::$threads[$pid];

        if (!$thread->isRunning()) return false;

        $thread->setStopped(true);

        return true;
    }

    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    public function getInput(): mixed
    {
        return self::$inputs[get_called_class()];
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    protected function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function isSignaled(): bool
    {
        return $this->signaled;
    }

    protected function setSignaled(bool $signaled): void
    {
        $this->signaled = $signaled;
    }

    abstract public function onRun(): void;

    /**
     * @param array<int, array<string>> $mode
     *
     * @return Promise
     * @throws ReflectionException
     * @throws Throwable
     * @phpstan-param array<int, array<string>> $mode
     * @phpstan-return Promise
     */
    public function start(array $mode = DescriptorSpec::BASIC): Promise
    {
        return new Promise(function ($resolve, $reject) use ($mode): mixed {
            $className = get_called_class();

            $reflection = new ReflectionClass($className);

            $class = $reflection->getFileName();

            $pathAutoLoad = __FILE__;
            $pathAutoLoad = str_replace(
                'src\vennv\vapm\Thread.php',
                'src\vendor\autoload.php',
                $pathAutoLoad
            );

            $input = self::$inputs[get_called_class()];

            if (is_string($input)) {
                $input = '\'' . self::$inputs[get_called_class()] . '\'';
            }

            if (is_callable($input) && $input instanceof Closure) {
                $input = Utils::closureToString($input);
                $input = Utils::removeComments($input);

                if (!is_string($input)) {
                    return $reject(new RuntimeException(ErrorMessage::INPUT_MUST_BE_STRING_OR_CALLABLE->value));
                }

                $input = Utils::outlineToInline($input);

                if (!is_string($input)) {
                    return $reject(new RuntimeException(ErrorMessage::INPUT_MUST_BE_STRING_OR_CALLABLE->value));
                }

                $input = Utils::fixInputCommand($input);

                if (!is_string($input)) {
                    return $reject(new RuntimeException(ErrorMessage::INPUT_MUST_BE_STRING_OR_CALLABLE->value));
                }
            }

            if (!is_string($input)) {
                return $reject(new RuntimeException(ErrorMessage::INPUT_MUST_BE_STRING_OR_CALLABLE->value));
            }

            $command = PHP_BINARY . ' -r "require_once \'' . $pathAutoLoad . '\'; include \'' . $class . '\'; $input = ' . $input . '; $class = new ' . static::class . '($input); $class->onRun();"';

            unset(self::$inputs[get_called_class()]);

            $process = proc_open(
                $command,
                $mode,
                $pipes
            );

            if (!is_resource($process)) {
                return $reject(new ThreadException(ErrorMessage::UNABLE_START_THREAD->value));
            }

            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $data = json_encode(self::getDataMainThread());

            if (is_string($data)) {
                fwrite($pipes[0], $data);
                fclose($pipes[0]);
            }

            while (proc_get_status($process)['running']) {
                $status = proc_get_status($process);

                if (!isset(self::$threads[$status['pid']])) {
                    $this->setPid($status['pid']);

                    self::$threads[$status['pid']] = $this;
                }

                $thread = self::$threads[$status['pid']];

                $thread->setExitCode($status['exitcode']);
                $thread->setRunning($status['running']);
                $thread->setSignaled($status['signaled']);
                $thread->setStopped($status['stopped']);

                if ($thread->isStopped()) {
                    proc_terminate($process);
                    break;
                }

                FiberManager::wait();
            }

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            if ($error !== '' && is_string($error)) {
                return $reject(new ThreadException($error));
            }

            if (!is_bool($output)) {
                $explode = explode(PHP_EOL, $output);

                foreach ($explode as $item) {
                    if (empty($item)) continue;

                    if (self::isPostMainThread($item)) {
                        self::loadSharedData($item);
                    }

                    if (self::isAlert($item)) {
                        self::loadAlert($item);
                    }
                }
            }

            proc_close($process);
            unset(self::$threads[$this->getPid()]);

            return $resolve(self::getAlert($output));
        });
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public static function getDataMainThread(): array
    {
        return self::$shared;
    }

    protected function setRunning(bool $isRunning): void
    {
        $this->isRunning = $isRunning;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    protected function setStopped(bool $stopped): void
    {
        $this->stopped = $stopped;
    }

    private static function isPostMainThread(string $data): bool
    {
        return explode('=>', $data)[0] === self::POST_MAIN_THREAD;
    }

    public static function loadSharedData(string $data): void
    {
        $data = explode('=>', $data);

        if ($data[0] === self::POST_MAIN_THREAD) {
            $result = json_decode($data[1], true);

            if (is_array($result)) {
                self::setShared(array_merge(self::$shared, $result));
            }
        }
    }

    /**
     * @param array<string, mixed> $shared
     *
     * @phpstan-param array<string, mixed> $shared
     */
    public static function setShared(array $shared): void
    {
        self::$shared = $shared;
    }

    private static function isAlert(string $data): bool
    {
        return explode('=>', $data)[0] === self::POST_ALERT_THREAD;
    }

    private static function loadAlert(string $data): void
    {
        $data = explode('=>', $data);

        if (isset($data[0]) && $data[0] != self::POST_ALERT_THREAD) return;

        $result = json_decode($data[1], true);

        echo $result . PHP_EOL;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @param false|string $data
     *
     * @return array<int, mixed>
     * @phpstan-return array<int, mixed>
     */
    private static function getAlert(false|string $data): array
    {
        $result = [];

        if (!is_string($data)) return $result;

        $explode = explode(PHP_EOL, $data);

        foreach ($explode as $item) {
            if (empty($item)) continue;

            $dataExplode = explode('=>', $data);

            if (!isset($dataExplode[0])) continue;

            if ($dataExplode[0] == self::POST_ALERT_THREAD) {
                $try = json_decode($dataExplode[1], true);

                $result[] = is_array($try) ? $try : $dataExplode[1];
            }
        }

        return $result;
    }
}
