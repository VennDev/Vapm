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

use vennv\vapm\Error;
use vennv\vapm\http\Protocol;
use vennv\vapm\http\Status;
use vennv\vapm\utils\Utils;
use Socket;
use function explode;
use function str_replace;
use function in_array;
use function count;
use function call_user_func;
use function is_callable;
use function json_encode;
use function gzinflate;
use function mb_convert_encoding;

interface RequestInterface {

    /**
     * @return Socket
     *
     * This method returns the client socket
     */
    public function getClient() : Socket;

    /**
     * @return string
     *
     * This method returns the request method
     */
    public function getMethod() : string;

    /**
     * @return string
     *
     * This method returns the request path
     */
    public function getPath() : string;

    /**
     * @return string
     *
     * This method returns the request protocol
     */
    public function getProtocol() : string;

    /**
     * @return int
     *
     * This method returns the request status
     */
    public function getStatus() : int;

    /**
     * @return array<int|float|string, mixed>
     *
     * This method returns the request arguments
     */
    public function getArgs() : array;

    /**
     * @param string ...$types
     * @return bool
     *
     * This method checks if the request accepts the type
     */
    public function accepts(string ...$types) : bool;

}

final class Request implements RequestInterface {

    private Response $response;

    private Express $express;

    private Socket $client;

    private string $method;

    private string $path;

    private string $dataClient;

    private string $protocol = Protocol::HTTP_1_1;

    private int $status = Status::OK;

    /**
     * @var array<int|float|string, mixed>
     */
    private array $args;

    /**
     * @var string|array<int|float|string, mixed>|object
     */
    public string|array|object $body;

    /**
     * @var string|array<int|float|string, mixed>|object
     */
    public string|array|object $params;

    /**
     * @var string|array<int|float|string, mixed>|object
     */
    public string|array|object $query;

    /**
     * @param Response $response
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     * @param array<int|float|string, mixed> $params
     * @param array<int|float|string, mixed> $query
     */
    public function __construct(
        Response $response,
        Express  $express,
        Socket   $client,
        string   $path,
        string   $dataClient,
        string   $method = '',
        array    $args = [],
        array    $params = [],
        array    $query = []
    ) {
        $this->response = $response;
        $this->express = $express;
        $this->client = $client;
        $this->path = $path;
        $this->dataClient = $dataClient;
        $this->method = $method;
        $this->args = $args;
        $this->params = $params;
        $this->body = $dataClient;

        if ($this->express->getOptionsJson()->enable) {
            $this->params = (object)$params;
            $this->query = (object)$query;
            $this->body = $this->bodyToJson();
        }
    }

    public function getClient() : Socket {
        return $this->client;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getPath() : string {
        return $this->path;
    }

    public function getProtocol() : string {
        return $this->protocol;
    }

    public function getStatus() : int {
        return $this->status;
    }

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array {
        return $this->args;
    }

    public function accepts(string ...$types) : bool {
        $accept = $this->getAccepts();

        if ($accept === null) {
            return true;
        }

        $accept = explode(',', $accept);

        foreach ($accept as $value) {
            $value = str_replace(' ', '', $value);

            if (in_array($value, $types, true)) {
                return true;
            }
        }

        return false;
    }

    private function getAccepts() : ?string {
        $headers = explode("\r\n", $this->dataClient);

        foreach ($headers as $header) {
            $header = explode(':', $header);

            if (count($header) === 2) {
                [$key, $value] = $header;

                if ($key === 'Accept') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @return string|object
     */
    private function bodyToJson() : string|object {
        $status = (string)Status::getStatusName($this->status);
        $data = [
            'method' => $this->method,
            'path' => $this->path,
            'protocol' => $this->protocol,
            'status' => $status,
            'args' => $this->args
        ];

        $headers = explode("\r\n", $this->dataClient);

        foreach ($headers as $header) {
            $header = explode(':', $header);

            if (count($header) === 2) {
                [$key, $value] = $header;

                $data[$key] = $value;
            }
        }

        $options = $this->express->getOptionsJson();

        if (Utils::getBytes($data) > $options->limit) {
            return Error::PAYLOAD_TOO_LARGE;
        }

        if ($options->reviver !== null && is_callable($options->reviver)) {
            foreach ($data as $key => $value) {
                $data[$key] = call_user_func($options->reviver, $key, $value);
            }
        }

        /**
         * @var array<int, string>|string $data
         */
        $encoding = mb_convert_encoding($data, 'UTF-8');

        if (!$options->strict) {
            $data = (object)$data;
        } else {
            $data = (string)json_encode($data);
        }

        if ($options->verify !== null && is_callable($options->verify)) {
            call_user_func(
                $options->verify,
                $this,
                $this->response,
                $data,
                $encoding
            );
        }

        if ($options->inflate) {
            if (is_object($data)) {
                $data = (string)json_encode($data);
            }

            $data = (string)gzinflate($data);
        }

        return $data;
    }

}