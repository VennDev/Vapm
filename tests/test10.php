<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function promise1() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($reject) {
            $reject("promise1");
        }, 5000);
    });
}

function promise2() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($reject) {
            $reject("promise2");
        }, 3000);
    });
}

function asyncTest() {
    new Async(function() {
        $promise = Async::await(Promise::any([
            promise1(),
            promise2()
        ]));
        var_dump($promise);
    });
}

asyncTest();

System::endSingleJob();