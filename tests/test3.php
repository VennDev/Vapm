<?php

require 'vendor/autoload.php';

use vennv\System;

function testAsync() {
    System::setTimeout(function() {
        echo "Hello World\n";
    }, 5000);
    var_dump("Hello");
}

testAsync();
System::endSingleJob();

