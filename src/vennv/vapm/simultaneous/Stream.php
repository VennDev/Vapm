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
use vennv\vapm\System;
use vennv\vapm\enums\ErrorMessage;
use vennv\api\simultaneous\StreamInterface;

use function fgets;
use function fopen;
use function touch;
use function fclose;
use function fwrite;
use function unlink;
use function file_exists;
use function stream_set_blocking;

final class Stream implements StreamInterface
{
    /**
     * @throws Throwable
     */
    public static function read(string $path): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path): void {
            $lines = '';
            $handle = fopen($path, 'r');

            if ($handle === false) {
                $reject(ErrorMessage::UNABLE_TO_OPEN_FILE->value);
                return;
            }

            stream_set_blocking($handle, false);

            while (($line = fgets($handle)) !== false) {
                $lines .= $line;
                FiberManager::wait();
            }

            fclose($handle);

            $resolve($lines);
        });
    }

    /**
     * @throws Throwable
     */
    public static function write(string $path, string $data): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path, $data): void {
            System::setTimeout(function () use ($resolve, $reject, $path, $data): void {
                $callback = function ($path, $data) use ($reject): void {
                    $handle = fopen($path, 'w');

                    if ($handle === false) {
                        $reject(ErrorMessage::UNABLE_TO_OPEN_FILE->value);
                        return;
                    }

                    stream_set_blocking($handle, false);
                    fwrite($handle, $data);
                    fclose($handle);
                };

                $callback($path, $data);

                $resolve();
            }, 0);
        });
    }

    /**
     * @throws Throwable
     */
    public static function append(string $path, string $data): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path, $data): void {
            System::setTimeout(function () use ($resolve, $reject, $path, $data): void {
                $callback = function ($path, $data) use ($reject): void {
                    $handle = fopen($path, 'a');

                    if ($handle === false) {
                        $reject(ErrorMessage::UNABLE_TO_OPEN_FILE->value);
                        return;
                    }

                    stream_set_blocking($handle, false);
                    fwrite($handle, $data);
                    fclose($handle);
                };

                $callback($path, $data);

                $resolve();
            }, 0);
        });
    }

    /**
     * @throws Throwable
     */
    public static function delete(string $path): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path): void {
            System::setTimeout(function () use ($resolve, $reject, $path): void {
                $callback = function ($path) use ($reject): void {
                    if (!file_exists($path)) {
                        $reject(ErrorMessage::FILE_DOES_NOT_EXIST->value);
                        return;
                    }

                    unlink($path);
                };

                $callback($path);

                $resolve();
            }, 0);
        });
    }

    /**
     * @throws Throwable
     */
    public static function create(string $path): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path): void {
            System::setTimeout(function () use ($resolve, $reject, $path): void {
                $callback = function ($path) use ($reject): void {
                    if (file_exists($path)) {
                        $reject(ErrorMessage::FILE_ALREADY_EXISTS->value);
                        return;
                    }

                    touch($path);
                };

                $callback($path);

                $resolve();
            }, 0);
        });
    }

    /**
     * @throws Throwable
     */
    public static function overWrite(string $path, string $data): Promise
    {
        return new Promise(function ($resolve, $reject) use ($path, $data): void {
            System::setTimeout(function () use ($resolve, $reject, $path, $data): void {
                $callback = function ($path, $data) use ($reject): void {
                    $handle = fopen($path, 'w+');

                    if ($handle === false) {
                        $reject(ErrorMessage::UNABLE_TO_OPEN_FILE->value);
                        return;
                    }

                    stream_set_blocking($handle, false);
                    fwrite($handle, $data);
                    fclose($handle);
                };

                $callback($path, $data);

                $resolve();
            }, 0);
        });
    }
}
