<?php

require 'vendor/autoload.php';

use vennv\Async;
use vennv\System;

System::start();
function fetchData($url) : mixed {
    return Async::create(function() use ($url) {
        $curl = curl_init();
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = Async::await(fn() => curl_exec($curl));

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }

        curl_close($curl);

        return $response;
    });
}

function test() {
    Async::create(function() {
        $url = [
            "https://www.google.com",
            "https://www.youtube.com"
        ];
        
        foreach ($url as $value) {
            $res = Async::await(fetchData($value));
            var_dump($res);
        }
    });
    var_dump("Hello World");
}

test();
System::end();