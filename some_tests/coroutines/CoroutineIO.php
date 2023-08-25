<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\simultaneous\CoroutineGen;
use vennv\vapm\simultaneous\CoroutineScope;
use vennv\vapm\simultaneous\Dispatchers;

$scope = new CoroutineScope(Dispatchers::IO);

$scope->launch(function () {
    $file = fopen(__DIR__ . "/test.txt", "w");
    fwrite($file, "Hello World! Nam");
    fclose($file);
});

$scope->launch(function () {
    echo "Coroutine 2\n";
});

CoroutineGen::runBlocking($scope);