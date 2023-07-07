<?php

require 'vendor/autoload.php';

use vennv\System;
use vennv\Promise;
use vennv\Async;

function curlRequest($url) {
    return new Promise(function($resolve, $reject) use ($url) {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        if(curl_errno($curl)) {
            $error_message = curl_error($curl);
            $reject($error_message);
        }

        curl_close($curl);
		
		var_dump($url);

        $resolve($response);
    });
}

function asyncTest() {
	new Async(function() {
		$time = microtime(true);
		Async::await(Promise::all([
			curlRequest("https://www.example.com"), 
			curlRequest("https://www.yahoo.com"), 
			curlRequest("https://www.google.com")
		]));
		var_dump(microtime(true) - $time);
	});
}

asyncTest();

System::endSingleJob();