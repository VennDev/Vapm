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

use RuntimeException;
use vennv\vapm\Async;
use vennv\vapm\http\Method;
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

    public function path() : string;

    public function setPath(string $path) : void;

    public function enable() : void;

    public function enabled() : bool;

    public function disable() : void;

    public function disabled() : bool;

    public function get(string $path, callable $callback) : void;

    public function use(string $path, callable $callback) : void;

    public function post(string $path, callable $callback) : void;

    public function put(string $path, callable $callback) : void;

    /**
     * @throws Throwable
     */
    public function listen(int $port, callable $callback) : void;

}

final class Express implements ExpressInterface {

    public const VERSION = '1.0.0-ALPHA5';

    /**
     * @var array<string|float|int, Routes>
     */
    private static array $routes = [];

    /**
     * @var array<string|float|int, callable>
     */
    private static array $middlewares = [];

    private static string $path = '';

    private static string $address = '127.0.0.1';

    private bool $enable = true;

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

    public function path() : string {
        return self::$path;
    }

    public function setPath(string $path) : void {
        self::$path = $path;
    }

    public function enable() : void {
        $this->enable = true;
    }

    public function enabled() : bool {
        return $this->enable;
    }

    public function disable() : void {
        $this->enable = false;
    }

    public function disabled() : bool {
        return !$this->enable;
    }

    public function get(string $path, callable $callback) : void {
        self::$routes[$path] = new Routes(Method::GET, $path, $callback);
    }

    public function use(string $path, callable $callback) : void {
        self::$middlewares[$path] = $callback;
    }

    public function post(string $path, callable $callback) : void {
        self::$routes[$path] = new Routes(Method::POST, $path, $callback);
    }

    public function put(string $path, callable $callback) : void {
        self::$routes[$path] = new Routes(Method::PUT, $path, $callback);
    }

    /**
     * @param Routes $route
     * @param Socket $client
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @return Async
     * @throws Throwable
     */
    private function processRoute(Routes $route, Socket $client, string $method, array $args = []) : Async {
        return new Async(function () use ($route, $client, $method, $args) : void {
            $callback = $route->getCallback();
            $methodRequire = $route->getMethod();

            if ($methodRequire === $method || $methodRequire === 'ALL') {
                $data = [$client, self::$path, $method, $args];

                $request = new Request(...$data);
                $response = new Response(...$data);

                Async::await(call_user_func($callback, $request, $response));
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function listen(int $port, callable $callback) : void {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            throw new RuntimeException('Error to create socket');
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, self::$address, $port);
        socket_listen($socket, 1);
        socket_set_nonblock($socket);

        $this->socket = $socket;

        call_user_func($callback);

        while ($this->enable) {
            $client = socket_accept($socket);

            if ($client !== false) {
                socket_getpeername($client, $address, $port);

                echo "New connection from " . $address . ":" . $port . "\n";

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

                        if (isset(self::$middlewares[$path])) {
                            Async::await(call_user_func(self::$middlewares[$path]));
                        }

                        if (isset(self::$routes[$path])) {
                            Async::await($this->processRoute(self::$routes[$path], $client, $method, $finalRequest));
                        }
                    }

                    socket_close($client);
                });
            }

            System::runEventLoop();
        }
    }

}