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

use vennv\vapm\http\Protocol;
use vennv\vapm\http\Status;
use Socket;
use function explode;
use function str_replace;
use function count;

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

    public function accept(string $type) : bool;

}

final class Request implements RequestInterface {

    private Socket $client;

    private string $method;

    private string $path;

    private string $protocol = Protocol::HTTP_1_1;

    private int $status = Status::OK;

    /**
     * @var array<int|float|string, mixed>
     */
    private array $args;

    /**
     * @param Socket $client
     * @param string $path
     * @param string $method
     * @param array<int|float|string, mixed> $args
     */
    public function __construct(Socket $client, string $path, string $method = '', array $args = []) {
        $this->client = $client;
        $this->method = $method;
        $this->path = $path;
        $this->args = $args;
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

    public function accept(string $type) : bool {
        $accept = $this->getHeader();
        if ($accept === null) {
            return false;
        }

        $accept = explode(',', $accept);
        foreach ($accept as $item) {
            if (str_replace(' ', '', $item) === $type) {
                return true;
            }
        }

        return false;
    }

    private function getHeader() : ?string {
        $headers = $this->getHeaders();

        foreach ($headers as $header) {
            if (str_replace(' ', '', $header[0]) === 'Accept') {
                return $header[1];
            }
        }

        return null;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function getHeaders() : array {
        $headers = [];
        $data = explode("\r\n", $this->getRawHeaders());

        foreach ($data as $header) {
            $header = explode(':', $header);

            if (count($header) === 2) {
                $headers[] = [$header[0], $header[1]];
            }
        }

        return $headers;
    }

    private function getRawHeaders() : string {
        $headers = '';
        $data = explode("\r\n\r\n", $this->getRaw());

        if (count($data) === 2) {
            $headers = $data[0];
        }

        return $headers;
    }

    private function getRaw() : string {
        $raw = '';
        $data = explode("\r\n\r\n", $this->get());

        if (count($data) === 2) {
            $raw = $data[1];
        }

        return $raw;
    }

    public function get() : string {
        $data = $this->getMethod() . ' ' . $this->getPath() . ' ' . $this->getProtocol() . "\r\n";
        $data .= $this->getRawHeaders() . "\r\n";
        $data .= $this->getRaw();

        return $data;
    }

}