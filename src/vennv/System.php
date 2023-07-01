<?php

namespace vennv;

final class System extends Timer {

    /**
     * This is a method used to make your program easier to understand 
     * where the program begins and where it ends.
     */
    public static function start() : void 
    {
        //Do nothing
    }

    /**
     * This method is usually used at the end of the whole chunk of your program, 
     * it is used to run the event loop.
     */
    public static function end() : void 
    {
        parent::run();
    }

    /**
     * This method is used to add a callable to the loop. The callable will
     * be executed after the timeOut.
     */
    public static function setTimeOut(callable $callable, float $timeOut) : void 
    {
        parent::setTimeOut(
            $callable, 
            $timeOut
        );
    }

}