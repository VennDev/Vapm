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

use vennv\vapm\System;
use vennv\vapm\simultaneous\enums\ErrorMessage;
use vennv\vapm\express\data\JsonData;
use vennv\vapm\express\data\StaticData;
use vennv\vapm\express\handlers\Request;
use vennv\vapm\express\handlers\Response;
use vennv\vapm\express\router\Router;
use vennv\vapm\simultaneous\Async;
use vennv\vapm\simultaneous\Error;
use vennv\vapm\http\TypeData;
use vennv\vapm\utils\Utils;
use RuntimeException;
use Exception;
use Socket;
use Throwable;
use function call_user_func;
use function count;
use function explode;
use function socket_accept;
use function socket_bind;
use function socket_create;
use function socket_listen;
use function socket_read;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_strerror;
use const AF_INET;
use const SOCK_STREAM;
use const SOL_TCP;

/**
 * This is version 1.0.0-ALPHA15 of Express
 * This is version still in development, so it is not recommended to use it in production
 */
interface ExpressInterface {

    /**
     * @return string
     *
     * This method will return the url of the server
     */
    public function getUrl() : string;

    /**
     * @return Router
     *
     * This method will return the new router of the server
     */
    public function router() : Router;

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
     * @return int
     *
     * This method will return the port of the server
     */
    public function getPort() : int;

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
     * @throws Throwable
     *
     * This method will start the server
     */
    public function listen(int $port, callable $callback) : void;

}

class Express extends Router implements ExpressInterface {

    public const LENGTH_BUFFER = 1024; // The length of the buffer to read the data

    private static string $address = '127.0.0.1';

    private static int $port = 3000;

    private bool $enable = true;

    private ?Socket $socket = null;

    public function __construct() {
        parent::__construct();
    }

    public function getUrl() : string {
        return 'http://' . self::$address . ':' . self::$port;
    }

    /**
     * @param array<string, mixed> $options
     * @throws Exception
     */
    public function router(array $options = []) : Router {
        return new Router($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getCallbackUpdateOptions(string $name, array $options) : callable {
        return function ($request, $response, $next) use ($name, $options) : mixed {
            $last = $this->options[$name];

            if ($last instanceof JsonData || $last instanceof StaticData) {
                $this->options[$name] = $last->update($last, $options);
            }

            return $next();
        };
    }

    /**
     * @param array<string, mixed> $options
     */
    public function json(array $options = ['enable' => true]) : callable {
        return $this->getCallbackUpdateOptions('json', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function static(array $options = ['enable' => true]) : callable {
        return $this->getCallbackUpdateOptions('static', $options);
    }

    public function getAddresses() : string {
        return self::$address;
    }

    public function setAddresses(string $address) : void {
        self::$address = $address;
    }

    public function getPort() : int {
        return self::$port;
    }

    public function getSockets() : ?Socket {
        return $this->socket;
    }

    public function setPath(string $path) : void {
        $this->path = $path;

        $dotFiles = TypeData::DOT_FILES_IGNORE;

        $options = $this->getOptionsStatic();

        if ($options->dotfiles === 'deny') {
            return;
        }

        foreach ($options->extensions as $extension) {
            // check have . in extension
            if (!str_contains($extension, '.')) {
                $extension = '.' . $extension;
            }

            $dotFiles[$extension] = TypeData::ALL;
        }

        if ($options->dotfiles === 'allow') {
            $dotFiles = array_merge($dotFiles, TypeData::DOT_FILES_MORE);
        }

        /**
         * @var string $dotFile
         * @var string $type
         */
        foreach ($dotFiles as $dotFile => $type) {
            /** @var string $file */
            foreach (Utils::getAllByDotFile($path, $dotFile) as $file) {
                $replacePath = str_replace([$path, '\\'], ['', '/'], $file);

                $this->get($replacePath, function ($req, $res) use ($replacePath, $type) {
                    $res->render($replacePath, true, false, ['Content-Type: ' . $type]);
                });
            }
        }
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
     * @throws Throwable
     */
    public function listen(int $port, callable $callback) : void {
        self::$port = $port;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            throw new RuntimeException(ErrorMessage::ERROR_TO_CREATE_SOCKET->value);
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        $bind = socket_bind($socket, self::$address, $port);
        if ($bind === false) {
            throw new RuntimeException(socket_strerror(socket_last_error($socket)));
        }

        $listen = socket_listen($socket, 1);
        if ($listen === false) {
            throw new RuntimeException(socket_strerror(socket_last_error($socket)));
        }

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
                        [$request, $response] = $this->getCallbackFromRequest($this, $client, $this->path(), $data, $method, $finalRequest);

                        Async::await($this->processWorks($this, $path, $request, $response, $client, $data, $method, $finalRequest));
                    }

                    socket_close($client);
                });
            }

            System::runEventLoop();

            usleep(1000);
        }
    }

}