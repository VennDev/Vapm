<?php

require 'vendor/autoload.php';

use vennv\Async;
use vennv\System;

System::start();
function testAsync() {
    new Async(function() {
        sleep(5);
    });
}

function test() {
	$time = microtime(true);
    testAsync();
    var_dump("Hello World 1");
	var_dump(microtime(true) - $time);
}

test();
System::end();