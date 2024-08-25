<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\CoroutineGen;
use vennv\vapm\Deferred;

CoroutineGen::runNonBlocking(
    function() : Generator {
        $deferredA = new Deferred(function (): Generator {
            return yield "Hello World! 1";
        });

        $deferredB = new Deferred(function (): Generator {
            return yield "Hello World! 2";
        });

        $deferredC = new Deferred(function (): Generator {
            return yield "Hello World! 3";
        });

        $result = yield from Deferred::awaitAny($deferredA, $deferredB, $deferredC);
        var_dump($result);
    },
    function () {
        var_dump("Hello World! 4");
    }
);
