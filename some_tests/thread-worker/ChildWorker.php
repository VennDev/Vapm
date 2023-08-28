<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\simultaneous\Work;
use vennv\vapm\simultaneous\Worker;

$work1 = new Work();
$work2 = new Work();

$work1->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 1';
});

$work1->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 2';
});

$work2->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 3';
});

$work2->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 4';
});

$options = ['threads' => 1];

$worker = new Worker($work1, $options);
$childWorker = new Worker($work2, $options);

// This will add a child worker to the main worker.
$worker->addWorker($childWorker, function (array $result, Worker $childWorker) : void {
    // Do something with the results of this child worker
    $childWorker->done(); // This will void memory leaks
});

$worker->run(function (array $result, Worker $worker) : void {
    var_dump($result);
    $worker->done(); // This will void memory leaks
});
