<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\simultaneous\Promise;
use vennv\vapm\System;

function testPromise1() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("A");
        }, 1000);
    });
}

function testPromise2() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("B");
        }, 1000);
    });
}

function testPromise3() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("C");
        }, 1000);
    });
}

function testPromise4() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("D");
        }, 1000);
    });
}

testPromise1()->then(function ($value) {
    var_dump($value);
    return testPromise2();
})->then(function ($value) {
    var_dump($value);
    return testPromise3();
})->then(function ($value) {
    var_dump($value);
    return testPromise4();
})->then(function ($value) {
    var_dump($value);
})->catch(function ($value) {
    var_dump($value);
})->finally(function() {
    var_dump("Complete!");
});