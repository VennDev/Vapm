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

use vennv\vapm\Async;
use vennv\vapm\AsyncInterface;
use vennv\vapm\express\Express;
use vennv\vapm\http\Protocol;
use vennv\vapm\http\Status;
use Socket;
use Throwable;
use Exception;
use function socket_write;
use function implode;
use function is_array;
use function is_string;
use function array_merge;
use function str_replace;
use function json_encode;
use function mime_content_type;
use function pathinfo;
use function ob_start;
use function ob_end_clean;
use function file_get_contents;
use function gmdate;
use function time;
use function is_dir;
use function stat;
use function md5;
use const PATHINFO_EXTENSION;

interface ResponseInterface {

    public function getClient() : Socket;

    public function getMethod() : string;

    public function getPath() : string;

    public function getProtocol() : string;

    public function getStatus() : int;

    public function status(int $status) : ResponseInterface;

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array;

    /**
     * @param string $key
     * @param string $value
     * @return void;
     */
    public function setHeader(string $key, string $value) : void;

    /**
     * @param string $path
     * @param bool $usePath
     * @param bool $justActive
     * @param array<int|float|string, mixed> $options
     * @return AsyncInterface
     * @throws Throwable
     */
    public function render(string $path, bool $usePath = true, bool $justActive = false, array $options = ['Content-Type: text/html']) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function active(string $path) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function redirect(string $path, int $status = Status::FOUND) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function send(string $data, int $status = Status::OK) : AsyncInterface;

    /**
     * @param array<int|float|string, mixed> $data
     * @throws Throwable
     */
    public function json(array $data, int $status = Status::OK) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function download(string $path, int $status = Status::OK) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function file(string $path, int $status = Status::OK) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function image(string $path, int $status = Status::OK) : AsyncInterface;

    /**
     * @throws Throwable
     */
    public function video(string $path, int $status = Status::OK) : AsyncInterface;

}

final class Response implements ResponseInterface {

    protected Express $express;

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
     * @var array<int, string>
     */
    private array $headers = [];

    /**
     * @param Express $express
     * @param Socket $client
     * @param string $path
     * @param string $method
     * @param array<int|float|string, mixed> $args
     */
    public function __construct(
        Express $express,
        Socket  $client,
        string  $path,
        string  $method = '',
        array   $args = []
    ) {
        $this->express = $express;
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

    public function status(int $status) : self {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array<int|float|string, mixed>
     */
    public function getArgs() : array {
        return $this->args;
    }

    /**
     * @param string $path
     * @param array<int|float|string, mixed> $options
     */
    private function buildHeader(string $path, array $options = ['Content-Type: text/html']) : void {
        $protocol = $this->protocol;
        $status = $this->status;
        $statusName = Status::getStatusName($status);
        $optionsStatic = $this->express->getOptionsStatic();

        $hasDirect = false;
        if ($optionsStatic->enable) {
            $file = $this->express->path() . $path;

            if (is_callable($optionsStatic->setHeaders)) {
                call_user_func($optionsStatic->setHeaders, $this, $path, stat($file));
            }

            if ($optionsStatic->immutable) {
                $options[] = 'Cache-Control: immutable';
            }

            if ($optionsStatic->lastModified) {
                $date = gmdate('D, d M Y H:i:s', time());
                $options[] = 'Last-Modified: ' . $date . ' GMT';
            }

            if ($optionsStatic->etag) {
                $md5 = md5($this->path);
                $options[] = 'ETag: ' . $md5;
            }

            $options[] = 'Cache-Control: max-age=' . $optionsStatic->maxAge;

            if ($optionsStatic->redirect && is_dir($file)) {
                $options[] = 'Location: ' . $this->express->getUrl() . '/';
                $options[] = 'Connection: close';
                $hasDirect = true;
            }
        }

        if ($status === Status::FOUND && !$hasDirect) {
            $options[] = 'Location: ' . $this->express->getUrl() . $path;
            $options[] = 'Connection: close';
        }

        if ($status === Status::OK) {
            $mime = mime_content_type($this->path);
            $options[] = 'Content-Type: ' . $mime;
        }

        $options = array_merge($options, $this->headers);

        $data = "$protocol $status $statusName\r\n" . implode("\r\n", $options) . "\r\n\r\n";

        socket_write($this->client, $data);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader(string $key, string $value) : void {
        $this->headers[] = $key . ': ' . $value;
    }

    /**
     * @param string $path
     * @param bool $usePath
     * @param bool $justActive
     * @param array<int|float|string, mixed> $options
     * @return AsyncInterface
     * @throws Throwable
     */
    public function render(
        string $path,
        bool   $usePath = true,
        bool   $justActive = false,
        array  $options = ['Content-Type: text/html']
    ) : AsyncInterface {
        if (!$justActive) $this->buildHeader($path, $options);

        return new Async(function () use ($path, $usePath, $justActive) : void {
            ob_start();

            if ($usePath) {
                require_once $this->path . $path;

                $function = str_replace(['/', '.php'], '', $path);

                if (function_exists($function)) {
                    $body = Async::await($function($this->args));
                } else {
                    $body = file_get_contents($this->path . $path);
                }
            } else {
                $body = Async::await($path);
            }

            if (is_array($body)) {
                foreach ($body as $value) {
                    /** @var string $data */
                    $data = Async::await($value);

                    if (!$justActive) socket_write($this->client, $data);
                }
            } else {
                if (!is_string($body)) {
                    throw new Exception('Body must be string');
                }

                if (!$justActive) socket_write($this->client, $body);
            }

            ob_end_clean();
        });
    }

    /**
     * @throws Throwable
     */
    public function active(string $path) : AsyncInterface {
        return $this->render($path, true, true);
    }

    /**
     * @throws Throwable
     */
    public function redirect(string $path, int $status = Status::FOUND) : AsyncInterface {
        $this->status = $status;
        return $this->render($path, false);
    }

    /**
     * @throws Throwable
     */
    public function send(string $data, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        return $this->render($data, false);
    }

    /**
     * @param array<int|float|string, mixed> $data
     * @throws Throwable
     */
    public function json(array $data, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        $encode = json_encode($data);

        if ($encode === false) {
            throw new Exception('JSON encode error');
        }

        return $this->render($encode, false, false, ['Content-Type: application/json']);
    }

    /**
     * @throws Throwable
     */
    public function download(string $path, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        return $this->render($path, true, false, ['Content-Type: application/octet-stream']);
    }

    /**
     * @throws Throwable
     */
    public function file(string $path, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        return $this->render($path, true, false, ['Content-Type: ' . mime_content_type($path)]);
    }

    /**
     * @throws Throwable
     */
    public function image(string $path, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        return $this->render($path, true, false, ['Content-Type: image/' . pathinfo($path, PATHINFO_EXTENSION)]);
    }

    /**
     * @throws Throwable
     */
    public function video(string $path, int $status = Status::OK) : AsyncInterface {
        $this->status = $status;
        return $this->render($path, true, false, ['Content-Type: video/' . pathinfo($path, PATHINFO_EXTENSION)]);
    }

}