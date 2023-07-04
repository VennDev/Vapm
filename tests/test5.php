<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function testA() {
    sleep(3);
    return 1;
}

function testB() {
    new Async(function () {
        var_dump("AAA");
        $result = Async::await(testA());
        var_dump($result);
        $result = Async::await(testA());
        var_dump($result);
    });
}

testB();

System::endSingleJob();

