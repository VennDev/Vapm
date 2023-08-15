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

interface RequestInterface {

    public function getClient() : Socket;

    public function getMethod() : string;

    public function getPath() : string;

    public function getProtocol() : string;

    public function getStatus() : int;

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array;

    public function accepts(string ...$types) : bool;

}

final class Request implements RequestInterface {

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
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $dataClient
     * @param string $method
     * @param array<int|float|string, mixed> $args
     */
    public function __construct(Express $express, Socket $client, string $path, string $dataClient, string $method = '', array $args = []) {
        $this->express = $express;
        $this->client = $client;
        $this->path = $path;
        $this->dataClient = $dataClient;
        $this->method = $method;
        $this->args = $args;
        $this->body = $dataClient;

        if ($this->getOptions()->enable === true) {
            $this->body = $this->toJson();
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

    private function getOptions() : JsonData {
        return $this->express->getOptions()['json'];
    }

    /**
     * @return string|object
     */
    private function toJson() : string|object {
        $data = [
            'method' => $this->method,
            'path' => $this->path,
            'protocol' => $this->protocol,
            'status' => $this->status,
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

        $options = $this->getOptions();

        if (Utils::getBytes($data) > $options->limit) {
            return Error::PAYLOAD_TOO_LARGE;
        }

        if ($options->reviver !== null && is_callable($options->reviver)) {
            foreach ($data as $key => $value) {
                $data[$key] = call_user_func($options->reviver, $key, $value);
            }
        }

        if (!$options->strict) {
            $data = (object)$data;
        } else {
            $data = json_encode($data);
        }

        if ($options->verify !== null && is_callable($options->verify)) {
            $data = call_user_func($options->verify, $data);
        }

        if ($options->inflate) {
            if (is_object($data)) {
                $data = json_encode($data);
            }

            $data = gzinflate($data);
        }

        return $data;
    }

}