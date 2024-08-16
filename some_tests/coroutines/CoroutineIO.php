<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\CoroutineGen;
use vennv\vapm\CoroutineScope;
use vennv\vapm\Dispatchers;

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
