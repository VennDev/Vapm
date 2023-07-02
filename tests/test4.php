<?php


require 'vendor/autoload.php';

use vennv\Async;
use vennv\Promise;
use vennv\System;

System::start();

function testPromise($mode) : mixed {
    return new Promise(function() use ($mode) {
        if ($mode == 1) {
            Promise::resolve(1);
        } else {
            Promise::reject(2);
        }
    });
}

function test() {
    new Async(function() {
        $await = Async::await(testPromise(2));
        var_dump($await);
    });
}

test();
System::end();