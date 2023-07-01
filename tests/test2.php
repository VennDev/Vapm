<?php

require 'vendor/autoload.php';

use vennv\Async;
use vennv\System;

System::start();
function testAsync() {
    Async::create(function() {
        Async::await(fn() => System::setTimeOut(function() {
            var_dump("Hello World 2");
        }, 1000));
    });
}

function test() {
    testAsync();
    var_dump("Hello World 1");
}

test();
System::end();