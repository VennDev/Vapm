<?php

require 'vendor/autoload.php';

use vennv\Async;

function fetchData($url) : mixed {
	
	usleep(1000);
    
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

function test() : Async { 
    return Async::create(function() {

        try {
            $url = [
                "https://www.google.com",
                "https://www.youtube.com"
            ];
            
            foreach ($url as $value) {
                var_dump(Async::await(fn() => fetchData($value)));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    });
}

test();