<?php

require 'vendor/autoload.php';
include "SimpleThread.php";

use vennv\vapm\Async;

function start() : Async {
    return new Async(function () : void {
        $class = new SimpleThread();
        $data = Async::await($class->start());
        var_dump($data);
    });
}

start();
