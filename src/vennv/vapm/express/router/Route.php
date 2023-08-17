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

namespace vennv\vapm\express\router;

use RuntimeException;
use function count;
use function is_callable;

final class Route {

    private string $method;

    private string $path;

    private mixed $callback;

    /**
     * @var array<int|float|string, mixed> $params
     */
    private array $params = [];

    private bool $isRouteSpecial = false; // This route is special, it is link route with params

    /**
     * @param string $method
     * @param string $path
     * @param callable $callback
     * @param array<int|float|string, mixed> $params
     */
    public function __construct(string $method, string $path, callable $callback, array $params = []) {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->params = $params;

        if (count($params) > 0) {
            $this->isRouteSpecial = true;
        }
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
            throw new RuntimeException("The callback is not callable");
        }

        return $this->callback;
    }

    /**
     * @return array<int|float|string, mixed>
     */
    public function getParams() : array {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function isRouteSpecial() : bool {
        return $this->isRouteSpecial;
    }

}