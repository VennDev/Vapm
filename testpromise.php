<?php

require 'vendor/autoload.php';

use vennv\Promise;

function fetchData($url) : mixed {
    
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
}

function test() : mixed { 
    
    $pro1 = new Promise(function() {
        return Promise::resolve(fetchData("https://www.youtube.com"));
    });

    $pro2 = new Promise(function() {
        return Promise::resolve(fetchData("https://www.google.com"));
    });

    return Promise::all([$pro1, $pro2]);
}

test()->then(function($result) {
    var_dump($result);
})->catch(function($error) {
    var_dump($error);
});