<?php

require 'vendor/autoload.php';

use vennv\Async;
use vennv\System;

function testAsync() {
    new Async(function() {
        $result = 0;
        for ($i = 0; $i < 1000; $i++) {
            $result++;
        }
        echo $result;
    });
    var_dump("AA");
}

testAsync();
System::endSingleJob();

