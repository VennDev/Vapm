<?php

require 'vendor/autoload.php';

include "test5.php";

use vennv\vapm\Async;
use vennv\vapm\System;

$class = new TestThread();
$class->start()->then(function ($data) {
    echo "Thread finished: " . implode(", ", $data) . "\n";
})->catch(function (Throwable $e) {
    echo "Thread failed: " . $e->getMessage() . "\n";
})->finally(function () {
    echo "Thread finished\n";
});

var_dump(11);

System::runSingleEventLoop();