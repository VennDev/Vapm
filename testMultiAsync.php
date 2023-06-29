<?php

require 'vendor/autoload.php';

use vennv\Async;

function fetchData($url) : mixed {
    $async = Async::create(function() use ($url) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($curl);

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }

        curl_close($curl);

        return $response;
    });
    return Async::await(fn() => $async);
}

function test() : mixed { 
    $async = Async::create(function() {
        $response = fetchData("https://www.google.com");
        return $response;
    });
    return Async::await(fn() => $async);
}

var_dump(test());