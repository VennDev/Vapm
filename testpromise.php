<?php

require 'vendor/autoload.php';

use vennv\Promise;

function function1() : mixed {
    return new Promise(function() {
        sleep(2);
        return Promise::resolve(6);
    });
}

function function2() : mixed {
    return new Promise(function() {
        sleep(2);
        return Promise::resolve(6);
    });
}

function test() : mixed { 
    return function1()->then(function($result) {
        var_dump($result);
        return function2()->then(function($result) {
            var_dump($result);
        });
    });
}

test();