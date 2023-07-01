<?php

namespace vennv;

class Timer {

    public const MINIMUM_WORKING_TIME = 0.0001;

    protected static function setTimeOut(callable $callable, float $timeOut) : void 
    {
        $timeOut += self::MINIMUM_WORKING_TIME;

        Loop::add(
            $callable, 
            $timeOut
        );
    }

    protected static function run() : void 
    {
        Loop::run(Loop::IS_TIMEOUT);
    }

}