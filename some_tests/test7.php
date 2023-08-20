<?php

use vennv\vapm\simultaneous\Async;
use vennv\vapm\System;

require 'vendor/autoload.php';

// This function is called when a client connects to the server
function listenClient($socket) : Async {
    return new Async(function () {
        // TODO: Implement this function
    });
}

$socket = null;

// This function is called every second, it checks if the socket is null,
// if it is, it calls listenClient()
System::setInterval(function () use (&$socket) {
    if ($socket === null) {
        listenClient($socket);
    }
}, 1000);
