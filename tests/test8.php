<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function promise1() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise1");
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

function promise3() : Async {
    return new Async(function() {
        return "A";
    });
}

function asyncTest() {
    new Async(function() {
		$time = microtime(true);
        $promise = Async::await(Promise::allSettled([
            promise1(),
            promise2(),
            promise3()
        ]));
		var_dump(microtime(true) - $time);
        var_dump($promise);
    });
}

asyncTest();

System::endSingleJob();
