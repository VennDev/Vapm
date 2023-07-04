<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function promise1() : Async {
    return new Async(function() {
        return "A";
    });
}

function promise2() : Async {
    return new Async(function() {
        return "B";
    });
}

function promise3() : Async {
    return new Async(function() {
        return "C";
    });
}

function promise4() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise4");
        }, 5000);
    });
}

function promise5() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise5");
        }, 7000);
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

function asyncTest1() {
    new Async(function() {
        $promise = Async::await(promise4());
        var_dump($promise);
    });
}

function asyncTest2() {
    new Async(function() {
        $promise = Async::await(promise5());
        var_dump($promise);
    });
}

asyncTest();
asyncTest2();
asyncTest1();

System::endSingleJob();

