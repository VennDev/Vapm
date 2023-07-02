<?php

namespace vennv;

interface InterfaceSystem {

    /**
     * This is a method used to make your program easier to understand
     * where the program begins and where it ends.
     */
    public static function start() : void;

    /**
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     */
    public static function end() : void;

    /**
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     *
     * This method is used when you want to run the event loop in a non-blocking way.
     * You should run this method in a separate thread and make it repeat every second.
     */
    public static function endNonBlocking() : void;

    /**
     * This method is used to add a callable to the loop. The callable will
     * be executed after the timeOut.
     */
    public static function setTimeOut(callable $callable, float $timeOut) : void;

}