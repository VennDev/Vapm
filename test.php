<?php

require 'vendor/autoload.php';

use vennv\Async;
use vennv\Promise;

function fetchData($url) : mixed {
    return Async::create(function() use ($url) {
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
}

function test() : mixed { 
    return new Promise(function() {
        return Promise::resolve(fetchData("https://www.google.com")->getResult());
    });
}

test()->then(function($result) {
    var_dump($result);
})->catch(function($error) {
    var_dump($error);
});