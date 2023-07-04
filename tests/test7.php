<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function promise() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve('Hello World');
        }, 3000);
    });
}

promise()->then(function($result) {
    echo $result . PHP_EOL;
});

System::endSingleJob();

