<?php

use vennv\vapm\simultaneous\GreenThread;

require 'vendor/autoload.php';

function thread (string $thread, int $loop) {
    $i = $loop;
    while ($i--)
    {
        var_dump("Thread '{$thread}' printing '{$thread}' for {$i} times!");
        GreenThread::sleep($thread, 1); // It will sleep in 1 second
    }
    var_dump("Thread '{$thread}' finished after printing '{$thread}' for {$loop} times!");
}

foreach(range('A', 'F') as $c) {
    GreenThread::register($c, 'thread', [$c, rand(5, 20)]);
}