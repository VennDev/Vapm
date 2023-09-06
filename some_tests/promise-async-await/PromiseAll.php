<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\simultaneous\Promise;
use vennv\vapm\System;

/**
 * @throws Throwable
 */
function testPromise1() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("A");
        }, 1000);
    });
}

/**
 * @throws Throwable
 */
function testPromise2() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($reject) {
            $reject("B");
        }, 1000);
    });
}

/**
 * @throws Throwable
 */
function main() : void {
    Promise::all([
        testPromise1(),
        testPromise2(),
    ])
    ->then(function ($values) {
        var_dump($values);
    })
    ->catch(function ($reason) {
        var_dump($reason);
    });
}

main();