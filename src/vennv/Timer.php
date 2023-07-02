<?php

namespace vennv;

use Throwable;

class Timer {

    public const MINIMUM_WORKING_TIME = 0.0001;

    /**
     * This method is used to add a callable to the loop. The callable will
     * be executed after the timeOut.
     */
    protected static function setTimeOut(callable $callable, float $timeOut) : void 
    {
        $timeOut += self::MINIMUM_WORKING_TIME;

        EventQueue::add(
            $callable, 
            $timeOut
        );
    }

    /**
     * @throws Throwable
     */
    protected static function run() : void
    {
        EventQueue::run(EventQueue::IS_TIMEOUT);
    }

    /**
     * @throws Throwable
     */
    protected static function runNonBlocking() : void
    {
        EventQueue::runNonBlocking();
    }

    protected static function runWithTick() : void
    {
        EventQueue::runWithTick();
    }

}