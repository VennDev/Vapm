<?php

/**
 * Vapm - A library for PHP about Async, Promise, Coroutine, GreenThread,
 *      Thread and other non-blocking methods. The method is based on Fibers &
 *      Generator & Processes, requires you to have php version from >= 8.1
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

namespace vennv\vapm\express;

final class Routes {

    private string $method;

    private string $path;

    private mixed $callback;

    /**
     * @param string $method
     * @param string $path
     * @param callable $callback
     */
    public function __construct(string $method, string $path, callable $callback) {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getMethod() : string {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath() : string {
        return $this->path;
    }

    /**
     * @return callable
     */
    public function getCallback() : callable {
        if (!is_callable($this->callback)) {
            throw new \RuntimeException("The callback is not callable");
        }

        return $this->callback;
    }

}