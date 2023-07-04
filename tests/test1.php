<?php

require 'vendor/autoload.php';

use vennv\Promise;
use vennv\System;

function testPromise1() : Promise {
    return new Promise(function () {
        Promise::resolve("A");
    });
}

function testPromise2() : Promise {
    return new Promise(function () {
        Promise::resolve("B");
    });
}

function testPromise3() : Promise {
    return new Promise(function () {
        Promise::resolve("C");
    });
}

function testPromise4() : Promise {
    return new Promise(function () {
        Promise::resolve("D");
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
});

System::endSingleJob();

