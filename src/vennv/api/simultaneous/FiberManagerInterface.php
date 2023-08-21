<?php

namespace vennv\api\simultaneous;

use Throwable;

interface FiberManagerInterface
{
    /**
     * @throws Throwable
     *
     * This is a function that waits for the current fiber to finish.
     */
    public static function wait(): void;
}