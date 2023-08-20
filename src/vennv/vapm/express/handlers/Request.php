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

namespace vennv\vapm\express\handlers;

use Socket;
use vennv\vapm\express\Express;
use vennv\vapm\http\Protocol;
use vennv\vapm\http\Status;
use vennv\vapm\simultaneous\Error;
use vennv\vapm\utils\Utils;
use function call_user_func;
use function count;
use function explode;
use function gzinflate;
use function in_array;
use function is_callable;
use function json_encode;
use function mb_convert_encoding;
use function str_replace;

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
     * @return array|object
     *
     * This method returns the request params
     */
    public function getParams() : array|object;

    /**
     * @return array|object
     *
     * This method returns the request queries
     */
    public function getQueries() : array|object;

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
     * @var array<int|float|string, mixed>|string $body
     */
    public array|string $body;

    /**
     * @var array<int|float|string, mixed> $params
     */
    public array $params;

    /**
     * @var array<int|float|string, mixed> $queries
     */
    public array $queries;

    /**
     * @param Response $response
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $params
     * @param array<int|float|string, mixed> $queries
     */
    public function __construct(
        Response $response,
        Express  $express,
        Socket   $client,
        string   $path,
        string   $dataClient,
        string   $method = '',
        array    $params = [],
        array    $queries = []
    ) {
        $this->response = $response;
        $this->express = $express;
        $this->client = $client;
        $this->path = $path;
        $this->dataClient = $dataClient;
        $this->method = $method;
        $this->params = $params;
        $this->queries = $queries;
        $this->body = $dataClient;
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

    public function getParams() : array|object {
        if ($this->express->getOptionsJson()->enable) {
            return json_decode(json_encode($this->params));
        } else {
            return $this->params;
        }
    }

    public function getQueries() : array|object {
        if ($this->express->getOptionsJson()->enable) {
            return json_decode(json_encode($this->queries));
        } else {
            return $this->queries;
        }
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
            'status' => $status
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