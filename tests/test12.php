<?php

require 'vendor/autoload.php';

use vennv\System;

$url = "https://www.google.com/";

System::fetch($url)->then(function($value) {
    var_dump($value);
})->catch(function($reason) {
    var_dump($reason);
});

System::endSingleJob();