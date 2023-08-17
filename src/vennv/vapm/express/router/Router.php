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

use vennv\vapm\http\Method;

interface RouterInterface {

    /**
     * @param string $path
     * @param mixed ...$args
     *
     * This method will register a route with method GET
     */
    public function get(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param mixed ...$args
     *
     * This method will register a route with method POST
     */
    public function post(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param mixed ...$args
     *
     * This method will register a route with method PUT
     */
    public function put(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param mixed ...$args
     *
     * This method will register a route with method ALL
     */
    public function all(string $path, mixed ...$args) : void;

}

class Router implements RouterInterface {

    /** @var array<int|float|string, Route> */
    public array $routes = [];

    private function createRoute(string $method, string $path, mixed ...$args) : ?Route {
        $lastArg = $args[count($args) - 1];

        if (is_callable($lastArg)) {
            $callback = $lastArg;
            $args = array_slice($args, 0, count($args) - 1);
        } else {
            $callback = fn() => $lastArg;
        }

        $canDo = true;
        foreach ($args as $arg) {
            if (is_callable($arg)) {
                call_user_func($arg);
            }

            if (is_bool($arg) && $arg === false) {
                $canDo = false;
            }
        }

        $params = explode('/:', $path);
        $path = $params[0];
        unset($params[0]);

        if ($canDo) {
            return new Route($method, $path, $callback, $params);
        }

        return null;
    }

    private function registerRoute(string $method, string $path, mixed ...$args) : void {
        $route = $this->createRoute($method, $path, ...$args);

        if ($route instanceof Route) {
            $this->routes[$route->getPath()] = $route;
        }
    }

    public function get(string $path, mixed ...$args) : void {
        $this->registerRoute(Method::GET, $path, ...$args);
    }

    public function post(string $path, mixed ...$args) : void {
        $this->registerRoute(Method::POST, $path, ...$args);
    }

    public function put(string $path, mixed ...$args) : void {
        $this->registerRoute(Method::PUT, $path, ...$args);
    }

    public function all(string $path, mixed ...$args) : void {
        $this->registerRoute(Method::ALL, $path, ...$args);
    }

}