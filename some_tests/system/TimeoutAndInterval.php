<?php

require 'vendor/autoload.php';

use vennv\vapm\System;

System::time();

System::setTimeout(function () : void {
    echo "A\n";
}, 1000);

System::timeEnd();

System::setInterval(function () : void {
    echo "B\n";
}, 1000);