<?php


require 'vendor/autoload.php';

use vennv\Promise;
use vennv\System;

System::start();

function testPromise($mode) : mixed {
    return new Promise(function() use ($mode) {
        if ($mode == 1) {
            Promise::resolve(1);
        } else {
            Promise::reject(1);
        }
    });
}

function test() {
    $promise = testPromise(2);
    $promise->then(function($value) {
        echo "resolve: $value\n";
    })->catch(function($value) {
        echo "reject: $value\n";
    });
}

test();
System::end();