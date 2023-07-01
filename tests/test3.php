<?php

use vennv\Promise;
use vennv\System;

require 'vendor/autoload.php';

System::start();
function fetchData($url) : mixed {
    return (new Promise(function() use ($url) {
        $curl = curl_init();
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($curl);

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return Promise::reject("Error: " . $error);
        }

        curl_close($curl);

        return Promise::resolve($response);
    }))->getResult();
}

function test() : mixed {
    
    $pr1 = new Promise(function() {
        return Promise::resolve("Hello");
    });

    $pr2 = new Promise(function() {
        return Promise::resolve("World");
    });

    return Promise::all([
        $pr1,
        $pr2
    ]);
}

test()->then(function($res) {
    var_dump($res);
});

System::end();