<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function testA() {
    return new Promise(function() {
        Promise::resolve("Hello World");
    });
}

function testB() {
    new Async(function () {
        $result = Async::await(testA());
        var_dump($result);
    });
}

testB();

System::endSingleJob();

