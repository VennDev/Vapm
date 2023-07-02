<?php

namespace vennv;

interface InterfacePromise {

    public function getId() : int;

    public function then(callable $callable) : Promise;

    public function catch(callable $callable) : Promise;

    public static function resolve(mixed $result) : void;

    public static function reject(mixed $result) : void;

}