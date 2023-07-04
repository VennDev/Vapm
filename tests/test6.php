<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function promise1() : Promise {
    return new Promise(function() {
        Promise::resolve("promise1");
    });
}

function promise2() : Promise {
    return new Promise(function() {
        Promise::resolve("promise2");
    });
}

function promise3() : Promise {
    return new Promise(function() {
        Promise::resolve("promise3");
    });
}

function asyncTest() {
    new Async(function() {
        $promise = Async::await(Promise::all([
            promise1(),
            promise2(),
            promise3()
        ]));
        var_dump($promise);
    });
}

asyncTest();

System::endSingleJob();

