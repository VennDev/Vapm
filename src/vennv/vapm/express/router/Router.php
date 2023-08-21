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

use vennv\vapm\express\Express;
use vennv\vapm\express\data\JsonData;
use vennv\vapm\express\data\RouterData;
use vennv\vapm\express\data\StaticData;
use vennv\vapm\express\handlers\Request;
use vennv\vapm\express\handlers\Response;
use vennv\vapm\express\middleware\MiddleWare;
use vennv\vapm\simultaneous\Async;
use vennv\vapm\http\Method;
use vennv\vapm\utils\Utils;
use RuntimeException;
use Throwable;
use Exception;
use Generator;
use Socket;
use function array_slice;
use function array_map;
use function array_merge;
use function array_keys;
use function array_filter;
use function call_user_func;
use function file_exists;
use function is_callable;
use function is_string;
use function is_bool;
use function strtolower;
use function str_replace;
use function explode;
use function count;
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
     * @var array<string|float|int, array<string|float|int, Router|MiddleWare>>
     */
    protected array $middlewares = ['*' => []];

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    private RouterData $routerData;

    /** @var array<int|float|string, Route> */
    protected array $routes = [];

    /**
     * @var array<string, mixed>
     */
    protected array $params = [];

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

    /**
     * @param string $path
     * @param mixed ...$args
     * @return array<int, mixed>
     */
    private function getResults(string $path, mixed ...$args) : array {
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

        return [$path, $params, $callback, $canDo];
    }

    private function createRoute(string $method, string $path, mixed ...$args) : ?Route {
        /**
         * @var string $path
         * @var array<int, string> $params
         * @var callable $callback
         * @var bool $canDo
         */
        [$path, $params, $callback, $canDo] = $this->getResults($path, ...$args);

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

    private function processRequest(MiddleWare|Route $middleWare, string $path, Request $request) : void {
        $requireA = $requireB = false;
        $countParams = count($middleWare->params);
        $childPaths = iterator_to_array(Utils::splitStringBySlash($path));
        $params = array_map(
            /**
             * @param int|string $index
             * @param mixed $path
             * @return string|null
             */
            function (int|string $index, mixed $path) use ($middleWare, &$requireA, &$requireB, &$countParams) : ?string {
                if (!is_string($path) || !is_int($index)) {
                    return null;
                }

                $requireC = $this->path != $path && $middleWare->path != $path;

                if ($this->path == $path) $requireA = true;

                if ($middleWare->path == $path) $requireB = true;

                if ($requireA && $requireB && $requireC && $countParams > 0) {
                    $countParams--;
                    return str_replace('/', '', $path);
                }

                return null;
            }, array_keys($childPaths), $childPaths
        );

        $params = array_filter($params, fn($value) => $value !== null);

        if (!$this->routerData->mergeParams) {
            $request->params = [];
        }

        foreach ($params as $value) {
            foreach ($middleWare->params as $param) {
                $request->params[$param] = $value;
            }
        }
    }

    /**
     * @param string $path
     * @param mixed $middleWare
     * @param Request $request
     * @param Response $response
     * @param bool $canNext
     * @return bool
     */
    private function processMiddleware(string $path, mixed $middleWare, Request $request, Response $response, bool &$canNext) : bool {
        if (!$middleWare instanceof MiddleWare) {
            return false;
        }

        if (!$this->getOptionsStatic()->fallthrough) {
            if (!file_exists($this->path())) {
                return false;
            }
        }

        $this->processRequest($middleWare, $path, $request);

        if (is_callable($middleWare->callback)) {
            $dataCallBack = call_user_func($middleWare->callback, $request, $response, fn() => self::NEXT);

            if ($dataCallBack !== self::NEXT) {
                return $canNext = false;
            }
        }

        return true;
    }

    /**
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
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
        array   $params = [],
        array   $queries = []
    ) : array {
        $response = new Response(
            $express, $client, $path, $method, $params
        );

        $request = new Request(
            $response, $express, $client, $path, $dataClient, $method, $params, $queries
        );

        return [$request, $response];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @param string $path
     * @param string $method
     * @param array<int|float|string, mixed> $queries
     * @return Async
     * @throws Throwable
     */
    private function processRoute(
        Request  &$request,
        Response &$response,
        Route    $route,
        string   &$path,
        string   $method,
        array    $queries = []
    ) : Async {
        return new Async(function () use (
            &$request, &$response, $route, &$path, $method, $queries
        ) : void {
            $callback = $route->getCallback();
            $methodRequire = $route->getMethod();

            $request->queries = array_merge($request->queries, $queries);

            $this->processRequest($route, $path, $request);

            if ($methodRequire === $method || $methodRequire === Method::ALL) {
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
        Request  &$request,
        Response &$response,
        bool     &$canNext
    ) : Async {
        return new Async(function () use (
            $path, &$request, &$response, &$canNext
        ) : void {
            foreach ($this->middlewares['*'] as $middleware) {
                $this->processMiddleware($path, $middleware, $request, $response, $canNext);
            }

            if (isset($this->middlewares[$path])) {
                foreach ($this->middlewares[$path] as $middleware) {
                    $result = $this->processMiddleware($path, $middleware, $request, $response, $canNext);

                    if (!$result) {
                        break;
                    }
                }
            }

            $realPaths = iterator_to_array(Utils::splitStringBySlash($path));
            foreach ($realPaths as $index => $realPath) {
                if (isset($this->middlewares[$realPath])) {
                    /** @var MiddleWare $middleware */
                    foreach ($this->middlewares[$realPath] as $middleware) {
                        if (count($middleware->params) > 0) {
                            for ($i = 0; $i < count($middleware->params); $i++) {
                                $indexParam = $middleware->params[$i + 1];
                                $value = $realPaths[$index + $i];

                                $request->params[$indexParam] = !is_array($value) && !is_string($value) ? null : str_replace('/', '', $value);
                            }
                        }

                        $result = $this->processMiddleware($path, $middleware, $request, $response, $canNext);

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
        Request  &$request,
        Response &$response,
        Socket   $client,
        string   $data,
        string   $method,
        array    $finalRequest
    ) : Async {
        return new Async(function () use (
            $express, $path, &$request, &$response, $client, $data, $method, $finalRequest
        ) : void {
            $queries = iterator_to_array($this->processQueries($path));
            $path = parse_url($path, PHP_URL_PATH);

            if (!is_string($path)) return;

            $canNext = true;
            Async::await($this->processMiddlewares($path, $request, $response, $canNext));

            if (!$canNext) return;

            $childPaths = iterator_to_array(Utils::splitStringBySlash($path));
            if (isset($this->routes[$path])) {
                Async::await($this->processRoute(
                    $request, $response, $this->routes[$path], $path, $method, $queries
                ));
            } else {
                foreach ($childPaths as $pth) {
                    if (isset($this->routes[$pth])) {
                        Async::await($this->processRoute(
                            $request, $response, $this->routes[$pth], $path, $method, $queries
                        ));
                        break;
                    }
                }
            }

            $request->params = array_merge($request->params, $this->params);
            $request->queries = array_merge($request->queries, $queries);

            foreach ($childPaths as $pth) {
                if (isset($this->middlewares[$pth])) {
                    foreach ($this->middlewares[$pth] as $middleware) {
                        if ($middleware instanceof Router) {
                            Async::await($middleware->processWorks(
                                $express, $path, $request, $response, $client, $data, $method, $finalRequest
                            ));
                        }
                    }
                }
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
            $this->middlewares['*'][] = new MiddleWare('*', $args[0], []);
        } else {
            $path = $args[0];
            $param = $args[1];

            if (!is_string($path)) {
                return;
            }

            /**
             * @var string $pathOther
             * @var array<int|float|string, mixed> $params
             * @var callable $callback
             * @var bool $canDo
             */
            [$pathOther, $params, $callback, $canDo] = $this->getResults($path, ...$args);

            if (count($params) > 0 && $canDo) {
                $this->middlewares[$pathOther][] = new MiddleWare($pathOther, $callback, $params);
            } else if (count($params) <= 0) {
                if (!isset($this->middlewares[$path])) {
                    $this->middlewares[$path] = [];
                }

                if ($param instanceof Router) {
                    $param->path = $path;
                    $this->middlewares[$path][] = $param;
                }

                if (is_callable($param)) {
                    $this->middlewares[$path][] = new MiddleWare($path, $param);
                }
            }
        }
    }

}