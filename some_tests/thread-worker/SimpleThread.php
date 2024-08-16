<?php

require 'vendor/autoload.php';

use vennv\vapm\Thread;

class SimpleThread extends Thread {

    public function onRun() : void {
        sleep(3);
        self::alert("Hello World!");
    }

}
