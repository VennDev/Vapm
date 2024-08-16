<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\Work;
use vennv\vapm\Worker;

$work = new Work();

$work->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 1';
});

$work->add(function () : string {
    // In this will work with thread.
    return 'Hello World! 2';
});

$worker = new Worker($work, ['threads' => 1]);

$worker->run(function (array $result, Worker $worker) : void {
    var_dump($result);
    $worker->done(); // This will void memory leaks
});
