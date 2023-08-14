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
use function socket_set_option;
use function explode;
use function is_bool;
use function is_callable;
use function array_slice;
use function count;
use function call_user_func;
use const AF_INET;
use const SOCK_STREAM;
use const SOL_TCP;

/**
 * This is version 1.0.0-ALPHA9 of Express
 */
interface ExpressInterface {

    /**
     * @param array<string, mixed> $options
     * @return callable
     *
     * This is a built-in middleware function in Express. It parses incoming requests with JSON payloads and is based on body-parser.
     */
    public function json(array $options = ['enable' => true]) : callable;

    /**
     * @param array<string, mixed> $options
     * @return callable
     *
     * This is a built-in middleware function in Express. It parses incoming requests with urlencoded payloads and is based on body-parser.
     */
    public function static(array $options = ['enable' => true]) : callable;

    /**
     * @return string
     *
     * This method will return the address of the server
     */
    public function getAddresses() : string;

    /**
     * @param string $address
     *
     * This method will set the address of the server
     */
    public function setAddresses(string $address) : void;

    /**
     * @return Socket|null
     *
     * This method will return the socket of the server
     */
    public function getSockets() : ?Socket;

    /**
     * @return string
     *
     * This method will return the path of the server
     */
    public function path() : string;

    /**
     * @param string $path
     *
     * This method will set the path of the server
     */
    public function setPath(string $path) : void;

    /**
     * @return void
     *
     * This method will enable the server
     */
    public function enable() : void;

    /**
     * @return bool
     *
     * This method will return the status of the server is enabled or not
     */
    public function enabled() : bool;

    /**
     * @return void
     *
     * This method will disable the server
     */
    public function disable() : void;

    /**
     * @return bool
     *
     * This method will return the status of the server is disabled or not
     */
    public function disabled() : bool;

    /**
     * @param string $path
     * @param callable ...$args
     *
     * This method will create a route with method GET
     */
    public function get(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param callable ...$args
     *
     * This method will create a route with method POST
     */
    public function post(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param callable ...$args
     *
     * This method will create a route with method PUT
     */
    public function put(string $path, mixed ...$args) : void;

    /**
     * @param string $path
     * @param callable ...$args
     *
     * This method will create a route with method ALL
     */
    public function all(string $path, mixed ...$args) : void;

    /**
     * @param string|callable ...$args
     */
    public function use(string|callable ...$args) : void;

    /**
     * @throws Throwable
     *
     * This method will start the server
     */
    public function listen(int $port, callable $callback) : void;

}

final class Express implements ExpressInterface {

    public const LENGTH_BUFFER = 1024; // The length of the buffer to read the data

    public const NEXT = 'next';

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $options = [
        'static' => [
            'maxAge' => 0,
            'immutable' => false,
            'redirect' => true,
            'index' => 'index.html',
            'extensions' => false,
            'setHeaders' => null,
            'fallthrough' => true,
            'cacheControl' => true,
            'dotfiles' => 'ignore',
            'etag' => true,
            'lastModified' => true,
            'enable' => false,
        ],
        'json' => [
            'inflate' => true,
            'strict' => true,
            'limit' => 100000,
            'type' => 'application/json',
            'verify' => null,
            'reviver' => null,
            'enable' => false,
        ]
    ];

    /**
     * @var array<string|float|int, Routes>
     */
    private static array $routes = [];

    /**
     * @var array<string|float|int, array<string|float|int, callable>>
     */
    private static array $middlewares = [
        '*' => []
    ];

    private static string $path = '';

    private static string $address = '127.0.0.1';

    private bool $enable = true;

    private ?Socket $socket = null;

    /**
     * @param array<string, mixed> $options
     */
    public function json(array $options = ['enable' => true]) : callable {
        return fn() => $this->options['json'] = array_merge($this->options['json'], $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function static(array $options = ['enable' => true]) : callable {
        return fn() => $this->options['static'] = array_merge($this->options['static'], $options);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getOptions() : array {
        return $this->options;
    }

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

    private function toRoute(string $method, string $path, mixed ...$args) : ?Routes {
        $lastArg = $args[count($args) - 1];

        if (is_callable($lastArg)) {
            $callback = $lastArg;
            $args = array_slice($args, 0, count($args) - 1);
        } else {
            $callback = fn() => $lastArg;
        }

        foreach ($args as $arg) {
            if (is_callable($arg)) {
                call_user_func($arg);
            }

            if (is_bool($arg) && $arg === false) {
                return null;
            }
        }

        return new Routes($method, $path, $callback);
    }

    public function get(string $path, mixed ...$args) : void {
        $route = $this->toRoute(Method::GET, $path, ...$args);
        if ($route !== null) self::$routes[$path] = $route;
    }

    public function post(string $path, mixed ...$args) : void {
        $route = $this->toRoute(Method::POST, $path, ...$args);
        if ($route !== null) self::$routes[$path] = $route;
    }

    public function put(string $path, mixed ...$args) : void {
        $route = $this->toRoute(Method::PUT, $path, ...$args);
        if ($route !== null) self::$routes[$path] = $route;
    }

    public function all(string $path, mixed ...$args) : void {
        $route = $this->toRoute(Method::ALL, $path, ...$args);
        if ($route !== null) self::$routes[$path] = $route;
    }

    public function use(string|callable ...$args) : void {
        if (is_callable($args[0])) {
            self::$middlewares['*'][] = $args[0];
        } else {
            $path = $args[0];
            $callback = $args[1];

            if (!isset(self::$middlewares[$path])) {
                self::$middlewares[$path] = [];
            }

            if (is_callable($callback)) {
                self::$middlewares[$path][] = $callback;
            }
        }
    }

    /**
     * @param string $data
     * @return array<int, mixed>
     */
    private function getRequestData(string $data) : array {
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

        return [$method, $path, $finalRequest];
    }

    /**
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @return array<int, Request|Response>
     */
    private function getCallbackFromRequest(Socket $client, string $path, string $dataClient, string $method, array $args = []) : array {
        $request = new Request($this, $client, $path, $dataClient, $method, $args);
        $response = new Response($client, $path, $method, $args);

        return [$request, $response];
    }

    /**
     * @param Routes $route
     * @param Socket $client
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @return Async
     * @throws Throwable
     */
    private function processRoute(Routes $route, Socket $client, string $dataClient, string $method, array $args = []) : Async {
        return new Async(function () use ($route, $client, $dataClient, $method, $args) : void {
            $callback = $route->getCallback();
            $methodRequire = $route->getMethod();

            if ($methodRequire === $method || $methodRequire === Method::ALL) {
                [$request, $response] = $this->getCallbackFromRequest($client, self::$path, $dataClient, $method, $args);
                Async::await(call_user_func($callback, $request, $response));
            }
        });
    }

    /**
     * @param callable $callback
     * @param Request $request
     * @param Response $response
     * @param bool $canNext
     * @return bool
     */
    private function processMiddlewares(callable $callback, Request $request, Response $response, bool &$canNext) : bool {
        $dataCallBack = call_user_func($callback, $request, $response, fn() => self::NEXT);

        if ($dataCallBack !== self::NEXT) {
            return $canNext = false;
        }

        return true;
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
                new Async(function () use ($client) : void {
                    $data = socket_read($client, self::LENGTH_BUFFER);

                    if ($data !== false) {
                        /**
                         * @var string $method
                         * @var string $path
                         * @var array<int|float|string, mixed> $finalRequest
                         */
                        [$method, $path, $finalRequest] = $this->getRequestData($data);

                        /**
                         * @var Request $request
                         * @var Response $response
                         */
                        [$request, $response] = $this->getCallbackFromRequest($client, $path, $data, $method, $finalRequest);

                        $canNext = true;
                        foreach (self::$middlewares['*'] as $middleware) {
                            $this->processMiddlewares($middleware, $request, $response, $canNext);
                        }

                        if (isset(self::$middlewares[$path])) {
                            foreach (self::$middlewares[$path] as $middleware) {
                                $result = $this->processMiddlewares($middleware, $request, $response, $canNext);

                                if (!$result) {
                                    break;
                                }
                            }
                        }

                        if (isset(self::$routes[$path]) && $canNext) {
                            Async::await($this->processRoute(self::$routes[$path], $client, $data, $method, $finalRequest));
                        }
                    }

                    socket_close($client);
                });
            }

            System::runEventLoop();

            usleep(1000);
        }
    }

}