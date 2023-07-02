<?php

namespace vennv;

use Fiber;
use Exception;
use Throwable;

interface InterfaceEventQueue {

    /**
     * @deprecated This method you should not use.
     */
    public static function generateId() : int;

    /**
     * @deprecated This method you should not use.
     */
    public static function add(callable $callable, float $time, bool $inPromise = true) : int;

    /**
     * @deprecated This method you should not use.
     */
    public static function remove(int $id) : bool;

    /**
     * @deprecated This method you should not use.
     */
    public static function get(int $id) : ?Queue;

    /**
     * @deprecated This method you should not use.
     */
    public static function doResult(int $id) : ?Queue;

    /**
     * @deprecated This method you should not use.
     */
    public static function runById(int $id) : void;

    /**
     * @deprecated This method you should not use.
     */
    public static function run(int $mode = EventQueue::IS_NOT_TIMEOUT) : void;

    /**
     * @deprecated This method you should not use.
     */
    public static function runNonBlocking() : void;

}