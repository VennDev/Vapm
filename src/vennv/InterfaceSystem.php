<?php

namespace vennv;

use Throwable;

interface InterfaceSystem {

    /**
     * @throws Throwable
     *
     * This method is used to run callback after a certain amount of time.
     */
    public static function setTimeout(callable $callable, int $timeout) : void;

    /**
     * @throws Throwable
     *
     * This method is used to run a single job.
     * It is used when you want to run the event loop in a blocking way.
     */
    public static function endSingleJob() : void;

    /**
     * @throws Throwable
     *
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     *
     * This method is used when you want to run the event loop in a non-blocking way.
     * You should run this method in a separate thread and make it repeat every second.
     */
    public static function endMultiJobs() : void;

}