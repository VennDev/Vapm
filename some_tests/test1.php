<?php

require_once __DIR__ . '/vendor/autoload.php';

use vennv\vapm\Async;
use vennv\vapm\Promise;
use vennv\vapm\System;

function request($type) {
    return new Promise(function ($resolve, $reject) use ($type) {
        System::setTimeout(function () use ($resolve, $reject, $type) {
            $type === 'success' ? $resolve('success') : $reject('error');
        }, 1000);
    });
}

function getData() {
    new Async(function () {
        [$data, $error] = Async::await(handleRequest(request('success')));
        if ($error) {
            echo $error;
        } else {
            echo $data;
        }
    });
}

function handleRequest(Promise $promise) {
    return $promise->then(fn($data) => [$data, null])->catch(fn($error) => [null, $error]);
}

getData();

System::runSingleEventLoop();