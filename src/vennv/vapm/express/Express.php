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

use vennv\vapm\Async;
use vennv\vapm\System;
use Throwable;
use Socket;
use function socket_accept;
use function socket_bind;
use function socket_create;
use function socket_listen;
use function socket_read;
use function socket_set_nonblock;
use const AF_INET;
use const SOCK_STREAM;
use const SOL_TCP;

interface ExpressInterface {

    public function getAddresses() : string;

    public function setAddresses(string $address) : void;

    public function getSockets() : ?Socket;

    public function listen(int $port, callable $callback) : void;

    public function Method() : string;

    public function path() : string;

    public function setPath(string $path) : void;

    public function get(string $path, callable $callback) : void;

    public function use(callable $callback, string $path = "/") : void;

    public function post(string $path, callable $callback) : void;

}

final class Express implements ExpressInterface {

    public const VERSION = '1.0.0-ALPHA';

    /**
     * @var array<string|float|int, Routes>
     */
    private static array $routes = [];

    /**
     * @var array<string|float|int, callable>
     */
    private static array $middlewares = [];

    private static string $path = '';

    private static string $method = '';

    private static string $address = '127.0.0.1';

    private ?Socket $socket = null;

    public function getAddresses() : string {
        return self::$address;
    }

    public function setAddresses(string $address) : void {
        self::$address = $address;
    }

    public function getSockets() : ?Socket {
        return $this->socket;
    }

    public function listen(int $port, callable $callback) : void {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, self::$address, $port);
        socket_listen($socket);
        socket_set_nonblock($socket);

        $this->socket = $socket;

        call_user_func($callback);

        while (true) {
            $client = socket_accept($socket);

            if ($client !== false) {
                new Async(function () use ($client) : void {
                    $data = socket_read($client, 1024);

                    if ($data !== false) {
                        $data = explode("\r\n", $data);

                        $dataRequest = explode(' ', $data[0]);
                        $finalRequest = explode(' ', $data[count($data) - 1]);
                        $finalRequest = explode('&', $finalRequest[0]);

                        foreach ($finalRequest as $value) {
                            $explode = explode('=', $value);
                            $finalRequest[$explode[0]] = $explode[1] ?? '';
                        }

                        $method = $dataRequest[0];
                        $path = $dataRequest[1];

                        self::$method = $method;

                        if (isset(self::$middlewares[$path])) {
                            Async::await(call_user_func(self::$middlewares[$path]));
                        }

                        if (isset(self::$routes[$path]) && $client instanceof Socket) {
                            Async::await($this->processRoute(self::$routes[$path], $client, $finalRequest));
                        }
                    } else {
                        if (isset(self::$routes["/"])) {
                            Async::await($this->processRoute(self::$routes["/"], $client));
                        }
                    }

                    socket_close($client);
                });
            }

            System::runEventLoop();
        }
    }

    /**
     * @param Routes $route
     * @param Socket $client
     * @param array<int|float|string, mixed> $args
     * @return Async
     * @throws Throwable
     */
    private function processRoute(Routes $route, Socket $client, array $args = []) : Async {
        return new Async(function () use ($route, $client, $args) : void {
            $callback = $route->getCallback();
            $method = $route->getMethod();

            if (is_callable($callback)) {
                $request = new Request($client, self::$path, $method, $args);
                $response = new Response($client, self::$path, $method, $args);

                Async::await(call_user_func($callback, $request, $response));
            }

            if (is_string($route)) {
                echo $route;
            }
        });
    }

    public function Method() : string {
        return self::$method;
    }

    public function path() : string {
        return self::$path;
    }

    public function setPath(string $path) : void {
        self::$path = $path;
    }

    public function get(string $path, callable $callback) : void {
        self::$routes[$path] = new Routes('GET', $path, $callback);
    }

    public function use(callable $callback, string $path = "/") : void {
        self::$middlewares[$path] = $callback;
    }

    public function post(string $path, callable $callback) : void {
        self::$routes[$path] = new Routes('POST', $path, $callback);
    }

}