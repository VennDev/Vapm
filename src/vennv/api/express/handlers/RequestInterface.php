<?php

namespace vennv\api\express\handlers;

use Socket;

interface RequestInterface
{
    /**
     * @return Socket
     *
     * This method returns the client socket
     */
    public function getClient(): Socket;

    /**
     * @return string
     *
     * This method returns the request method
     */
    public function getMethod(): string;

    /**
     * @return string
     *
     * This method returns the request path
     */
    public function getPath(): string;

    /**
     * @return string
     *
     * This method returns the request protocol
     */
    public function getProtocol(): string;

    /**
     * @return int
     *
     * This method returns the request status
     */
    public function getStatus(): int;

    /**
     * @return mixed
     *
     * This method returns the request params
     */
    public function getParams(): mixed;

    /**
     * @return mixed
     *
     * This method returns the request queries
     */
    public function getQueries(): mixed;

    /**
     * @return string|object
     */
    public function getBody(): string|object;

    /**
     * @param string ...$types
     *
     * @return bool
     *
     * This method checks if the request accepts the type
     */
    public function accepts(string ...$types): bool;
}
