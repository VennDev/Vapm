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

use Generator;
use Socket;
use vennv\vapm\Async;
use vennv\vapm\express\data\JsonData;
use vennv\vapm\express\data\RouterData;
use vennv\vapm\express\data\StaticData;
use vennv\vapm\express\Express;
use vennv\vapm\express\handlers\Request;
use vennv\vapm\express\handlers\Response;
use vennv\vapm\http\Method;
use RuntimeException;
use Throwable;
use Exception;
use vennv\vapm\utils\Utils;
use function array_slice;
use function call_user_func;
use function file_exists;
use function is_callable;
use function is_string;
use function is_bool;
use function strtolower;
use function array_merge;
use function str_replace;
use function explode;
use function count;
use function end;
use function parse_url;
use function iterator_to_array;
use const PHP_URL_PATH;

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

    /**
     * @param string|callable|Router ...$args
     *
     * This method will register a middleware
     */
    public function use(string|callable|Router ...$args) : void;

    /**
     * @return JsonData
     *
     * This method will return the json data of the server
     */
    public function getOptionsJson() : JsonData;

    /**
     * @return StaticData
     *
     * This method will return the static data of the server
     */
    public function getOptionsStatic() : StaticData;

}

class Router implements RouterInterface {

    public const NEXT = 'next';

    /**
     * @var array<string|float|int, array<string|float|int, callable|Router>>
     */
    protected array $middlewares = ['*' => []];

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    private RouterData $routerData;

    /** @var array<int|float|string, Route> */
    protected array $routes = [];

    protected string $path = '';

    /**
     * @param array<string, mixed> $options
     * @throws Exception
     */
    public function __construct(array $options = []) {
        $this->options['static'] = new StaticData();
        $this->options['json'] = new JsonData();

        $this->routerData = new RouterData();

        /**
         * @var RouterData $update
         */
        $update = $this->routerData->update($this->routerData, $options);
        $this->routerData = $update;
    }

    private function createRoute(string $method, string $path, mixed ...$args) : ?Route {
        $options = $this->routerData;

        if ($options->caseSensitive) {
            $path = strtolower($path);
        }

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

        if ($options->mergeParams) {
            $params = array_merge($params, explode('/', $path));
        }

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

    /**
     * @return array<string, mixed>
     */
    private function getOptions() : array {
        return $this->options;
    }

    public function path() : string {
        return $this->path;
    }

    public function getOptionsJson() : JsonData {
        $result = $this->getOptions()['json'];

        if (!$result instanceof JsonData) {
            throw new RuntimeException('Invalid json options');
        }

        return $result;
    }

    public function getOptionsStatic() : StaticData {
        $result = $this->getOptions()['static'];

        if (!$result instanceof StaticData) {
            throw new RuntimeException('Invalid static options');
        }

        return $result;
    }

    /**
     * @param array<int|float|string, Route> $routes
     * @param string $path
     * @param array<int, string> $samplePaths
     * @return Generator
     */
    private function processChildPath(array $routes, string $path, array $samplePaths) : Generator {
        $route = $routes[$path];

        if (!isset($routes[$path]) && !$route->isRouteSpecial()) {
            yield 'null' => null;
        }

        $lastIndex = end($samplePaths);
        if ($lastIndex === false) {
            $lastIndex = [];
        }

        $params = [];
        $lastPath = str_replace($path, '', $lastIndex);
        if (is_string($lastPath)) {
            $params = explode('/', $lastPath);
        } else {
            foreach ($lastPath as $key => $value) {
                $params[$key] = explode('/', $value);
            }
        }

        foreach ($route->getParams() as $key => $param) {
            if (!isset($params[$key])) {
                continue;
            }

            yield $param => $params[$key];
        }
    }

    private function processQueries(string $path) : Generator {
        $queries = parse_url($path, PHP_URL_QUERY);

        if (is_string($queries)) {
            $explode = explode('&', $queries);

            foreach ($explode as $query) {
                $explodeQuery = explode('=', $query);

                if (count($explodeQuery) === 2) {
                    yield $explodeQuery[0] => $explodeQuery[1];
                }
            }
        }
    }

    /**
     * @param callable $callback
     * @param Request $request
     * @param Response $response
     * @param bool $canNext
     * @return bool
     */
    private function processMiddleware(callable $callback, Request $request, Response $response, bool &$canNext) : bool {
        if (!$this->getOptionsStatic()->fallthrough) {
            if (!file_exists($this->path())) {
                return false;
            }
        }

        $dataCallBack = call_user_func($callback, $request, $response, fn() => self::NEXT);

        if ($dataCallBack !== self::NEXT) {
            return $canNext = false;
        }

        return true;
    }

    /**
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @param array<int|float|string, mixed> $params
     * @param array<int|float|string, mixed> $queries
     * @return array<int, Request|Response>
     */
    public function getCallbackFromRequest(
        Express $express,
        Socket  $client,
        string  $path,
        string  $dataClient,
        string  $method,
        array   $args = [],
        array   $params = [],
        array   $queries = []
    ) : array {
        $response = new Response(
            $express, $client, $path, $method, $args
        );

        $request = new Request(
            $response, $express, $client, $path, $dataClient, $method, $args, $params, $queries
        );

        return [$request, $response];
    }

    /**
     * @param Express $express
     * @param Route $route
     * @param Socket $client
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @param array<int|float|string, mixed> $params
     * @param array<int|float|string, mixed> $queries
     * @return Async
     * @throws Throwable
     */
    private function processRoute(
        Express $express,
        Route   $route,
        Socket  $client,
        string  $dataClient,
        string  $method,
        array   $args = [],
        array   $params = [],
        array   $queries = []
    ) : Async {
        return new Async(function () use (
            $express, $route, $client, $dataClient, $method, $args, $params, $queries
        ) : void {
            $callback = $route->getCallback();
            $methodRequire = $route->getMethod();

            if ($methodRequire === $method || $methodRequire === Method::ALL) {
                [$request, $response] = $this->getCallbackFromRequest(
                    $express, $client, $this->path, $dataClient, $method, $args, $params, $queries
                );

                Async::await(call_user_func($callback, $request, $response));
            }
        });
    }

    /**
     * @param string $path
     * @param Request $request
     * @param Response $response
     * @param bool $canNext
     * @return Async
     * @throws Throwable
     */
    private function processMiddlewares(
        string   $path,
        Request  $request,
        Response $response,
        bool     &$canNext
    ) : Async {
        return new Async(function () use (
            $path, $request, $response, &$canNext
        ) : void {
            foreach ($this->middlewares['*'] as $middleware) {
                if (!is_callable($middleware)) {
                    continue;
                }

                $this->processMiddleware($middleware, $request, $response, $canNext);
            }

            if (isset($this->middlewares[$path])) {
                foreach ($this->middlewares[$path] as $middleware) {
                    if (!$middleware instanceof Router) {
                        $result = $this->processMiddleware($middleware, $request, $response, $canNext);

                        if (!$result) {
                            break;
                        }
                    }
                }
            }
        });
    }

    /**
     * @param Express $express
     * @param array<int|float|string, Route> $routes
     * @param string $path
     * @param bool $canNext
     * @param Socket $client
     * @param string $data
     * @param string $method
     * @param array<int|string, mixed> $finalRequest
     * @return Async
     * @throws Throwable
     */
    private function processPath(
        Express $express,
        array   $routes,
        string  $path,
        bool    $canNext,
        Socket  $client,
        string  $data,
        string  $method,
        array   $finalRequest
    ) : Async {
        return new Async(function () use (
            $express, $routes, $path, $canNext, $client, $data, $method, $finalRequest
        ) : bool {
            $realPath = parse_url($path, PHP_URL_PATH);
            if (is_string($realPath)) {
                $realPaths = Utils::splitStringBySlash($realPath);
            } else {
                $realPaths = [];
            }

            /** @var array<int|float|string, mixed> $queriesResult */
            $queriesResult = iterator_to_array($this->processQueries($path));

            unset($realPaths[0]); // Remove first element

            /** @var string $samplePath */
            foreach ($realPaths as $samplePath) {
                if (isset($routes[$samplePath]) && $canNext) {
                    $route = $routes[$samplePath];

                    /** @var array<int|float|string, mixed> $resultParams */
                    $resultParams = iterator_to_array($this->processChildPath($routes, $samplePath, $realPaths));

                    if (isset($resultParams['null'])) {
                        continue;
                    }

                    if (count($resultParams) < count($route->getParams())) {
                        // If the number of parameters is less than the number of parameters required by the route, continue
                        continue;
                    }

                    Async::await($this->processRoute($express, $route, $client, $data, $method, $finalRequest, $resultParams, $queriesResult));
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param Express $express
     * @param string $path
     * @param Request $request
     * @param Response $response
     * @param Socket $client
     * @param string $data
     * @param string $method
     * @param array<int|float|string, mixed> $finalRequest
     * @return Async
     * @throws Throwable
     */
    public function processWorks(
        Express  $express,
        string   $path,
        Request  $request,
        Response $response,
        Socket   $client,
        string   $data,
        string   $method,
        array    $finalRequest
    ) : Async {
        return new Async(function () use (
            $express, $path, $request, $response, $client, $data, $method, $finalRequest
        ) : void {
            $canNext = true;

            Async::await($this->processMiddlewares($path, $request, $response, $canNext));

            $realPaths = Utils::splitStringBySlash($path);
            unset($realPaths[0]); // Remove first element

            foreach ($realPaths as $realPath) {
                if (isset($this->middlewares[$realPath])) {
                    foreach ($this->middlewares[$realPath] as $router) {
                        if ($router instanceof Router) {
                            $childPath = Utils::replacePath($path, $realPath);
                            if ($childPath === false) {
                                continue;
                            }

                            Async::await($router->processWorks($express, $childPath, $request, $response, $client, $data, $method, $finalRequest));
                        }
                    }
                }
            }

            if (isset($this->routes[$path]) && $canNext) {
                Async::await($this->processRoute($express, $this->routes[$path], $client, $data, $method, $finalRequest));
            } else {
                Async::await($this->processPath($express, $this->routes, $path, $canNext, $client, $data, $method, $finalRequest));
            }
        });
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

    /**
     * @param string|callable|Router ...$args
     */
    public function use(string|callable|Router ...$args) : void {
        if (is_callable($args[0])) {
            $this->middlewares['*'][] = $args[0];
        } else {
            $path = $args[0];
            $param = $args[1];

            if (!is_string($path)) {
                throw new RuntimeException('Invalid path');
            }

            if (!isset($this->middlewares[$path])) {
                $this->middlewares[$path] = [];
            }

            if (is_callable($param) || $param instanceof Router) {
                $this->middlewares[$path][] = $param;
            }
        }
    }

}