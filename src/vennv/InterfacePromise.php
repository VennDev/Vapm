<?php

namespace vennv;

interface InterfacePromise {

    /**
     * This is method getId for get id point of promise
     */
    public function getId() : int;

    /**
     * This is method then for add callback to promise
     */
    public function then(callable $callable) : Promise;

    /**
     * This is method catch for add callback to promise
     */
    public function catch(callable $callable) : Promise;

    /**
     * This is method finally for add callback to promise
     */
    public static function resolve(mixed $result) : void;

    /**
     * This is method finally for add callback to promise
     */
    public static function reject(mixed $result) : void;

}
