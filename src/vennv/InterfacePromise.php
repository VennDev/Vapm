<?php

namespace vennv;

use Throwable;

interface InterfacePromise
{

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved.
     */
    public function then(callable $callable) : Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is rejected.
     */
    public function catch(callable $callable) : Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function resolve(int $id, mixed $result) : void;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function reject(int $id, mixed $result) : void;

    /**
     * @throws Throwable
     *
     * This method is used to add a callback|Promise|Async to the event loop.
     */
    public static function all(array $promises) : Promise;

    /**
     * @throws Throwable
     *
     * This method is used to get the id of the promise.
     */
    public function getId() : int;

}