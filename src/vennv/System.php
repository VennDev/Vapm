<?php

declare(ticks = 1);

namespace vennv;

use Throwable;

final class System extends Timer implements InterfaceSystem {

    public static function start() : void 
    {
        //Do nothing
    }

    /**
     * @throws Throwable
     */
    public static function end() : void
    {
        parent::run();
    }

    /**
     * @throws Throwable
     */
    public static function endNonBlocking() : void
    {
        parent::runNonBlocking();
    }

    public static function setTimeOut(callable $callable, float $timeOut) : void 
    {
        parent::setTimeOut(
            $callable, 
            $timeOut
        );
    }

}