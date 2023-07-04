<?php

namespace vennv;

use Fiber;
use Throwable;

final class System extends EventQueue
{

    /**
     * @throws Throwable
     */
    public static function setTimeout(callable $callable, int $timeout) : void
    {
        parent::addQueue(new Fiber($callable), false, Utils::milliSecsToSecs($timeout));
    }

    /**
     * @throws Throwable
     */
    public static function endSingleJob() : void
    {
        parent::runSingleJob();
    }

    /**
     * @throws Throwable
     */
    public static function endMultiJobs() : void
    {
        parent::runMultiJobs();
    }

}