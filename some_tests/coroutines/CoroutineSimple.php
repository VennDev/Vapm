<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\CoroutineGen;
use vennv\vapm\System;

System::time();
CoroutineGen::runBlocking(
    function() : Generator {
        yield from CoroutineGen::delay(3000);
        var_dump("A");
    },
    CoroutineGen::repeat(function() {
        yield from CoroutineGen::delay(100);
        var_dump("B");
    }, 5),
    function() {
        var_dump("C");
    },
    $launch = CoroutineGen::launch(
        CoroutineGen::launch(function() {
            yield from CoroutineGen::delay(1000);
            var_dump("D");
        })
    ),
    function() use ($launch) : Generator {
        yield from CoroutineGen::delay(3000);
        $launch->cancel();
        var_dump("E");
    },
    CoroutineGen::launch(
        CoroutineGen::launch(function() : void {
            var_dump("F");
        }),
        function() {
            var_dump("G");
        }
    ),
    function() {
        var_dump("H");
    }
);
System::timeEnd();
