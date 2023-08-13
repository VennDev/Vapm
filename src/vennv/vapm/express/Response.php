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

use Exception;
use vennv\vapm\Async;
use vennv\vapm\http\Protocol;
use vennv\vapm\http\Status;
use Socket;
use Throwable;
use function socket_write;
use function implode;
use function is_array;
use function str_replace;
use function json_encode;
use function mime_content_type;
use function pathinfo;
use const PATHINFO_EXTENSION;

interface ResponseInterface {

    public function getClient() : Socket;

    public function getMethod() : string;

    public function getPath() : string;

    public function getProtocol() : string;

    public function getStatus() : int;

    public function status(int $status) : void;

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array;

    /**
     * @param string $path
     * @param array<int|float|string, mixed> $options
     * @return Async
     * @throws Throwable
     */
    public function render(string $path, array $options = ['Content-Type: text/html']) : Async;

    /**
     * @throws Throwable
     */
    public function redirect(string $path, int $status = Status::FOUND) : void;

    /**
     * @throws Throwable
     */
    public function send(string $data, int $status = Status::OK) : void;

    /**
     * @param array<int|float|string, mixed> $data
     * @throws Throwable
     */
    public function json(array $data, int $status = Status::OK) : void;

    /**
     * @throws Throwable
     */
    public function download(string $path, int $status = Status::OK) : void;

    /**
     * @throws Throwable
     */
    public function file(string $path, int $status = Status::OK) : void;

    /**
     * @throws Throwable
     */
    public function image(string $path, int $status = Status::OK) : void;

    /**
     * @throws Throwable
     */
    public function video(string $path, int $status = Status::OK) : void;

}

final class Response implements ResponseInterface {

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
     * @param string $method
     * @param string $path
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

    public function status(int $status) : void {
        $this->status = $status;
    }

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array {
        return $this->args;
    }

    /**
     * @param array<int|float|string, mixed> $options
     */
    private function buildHeader(array $options = ['Content-Type: text/html']) : void {
        $protocol = $this->protocol;
        $status = $this->status;
        $statusName = Status::getStatusName($status);

        $data = "$protocol $status $statusName\r\n" . implode("\r\n", $options) . "\r\n\r\n";

        socket_write($this->client, $data);
    }

    /**
     * @param string $path
     * @param array<int|float|string, mixed> $options
     * @return Async
     * @throws Throwable
     */
    public function render(string $path, array $options = ['Content-Type: text/html']) : Async {
        $this->buildHeader($options);

        return new Async(function () use ($path) : void {
            require_once $this->path . $path;

            $function = str_replace(['/', '.php'], '', $path);

            if (function_exists($function)) {
                $body = Async::await($function($this->args));
            } else {
                throw new Exception("Cannot find function 'main' for $path, example: index.php -> index()");
            }

            if (is_array($body)) {
                foreach ($body as $value) {
                    /** @var string $data */
                    $data = Async::await($value);

                    socket_write($this->client, $data);
                }
            } else {
                if (!is_string($body)) {
                    throw new Exception('Body must be string');
                }

                socket_write($this->client, $body);
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function redirect(string $path, int $status = Status::FOUND) : void {
        $this->status = $status;
        $this->render($path);
    }

    /**
     * @throws Throwable
     */
    public function send(string $data, int $status = Status::OK) : void {
        $this->status = $status;
        $this->render($data);
    }

    /**
     * @param array<int|float|string, mixed> $data
     * @throws Throwable
     */
    public function json(array $data, int $status = Status::OK) : void {
        $this->status = $status;
        $encode = json_encode($data);

        if ($encode === false) {
            throw new Exception('JSON encode error');
        }

        $this->render($encode, ['Content-Type: application/json']);
    }

    /**
     * @throws Throwable
     */
    public function download(string $path, int $status = Status::OK) : void {
        $this->status = $status;
        $this->render($path, ['Content-Type: application/octet-stream']);
    }

    /**
     * @throws Throwable
     */
    public function file(string $path, int $status = Status::OK) : void {
        $this->status = $status;
        $this->render($path, ['Content-Type: ' . mime_content_type($path)]);
    }

    /**
     * @throws Throwable
     */
    public function image(string $path, int $status = Status::OK) : void {
        $this->status = $status;
        $this->render($path, ['Content-Type: image/' . pathinfo($path, PATHINFO_EXTENSION)]);
    }

    /**
     * @throws Throwable
     */
    public function video(string $path, int $status = Status::OK) : void {
        $this->status = $status;
        $this->render($path, ['Content-Type: video/' . pathinfo($path, PATHINFO_EXTENSION)]);
    }

}