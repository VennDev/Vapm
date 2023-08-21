<?php

namespace vennv\api\simultaneous;

use Throwable;

interface AsyncInterface
{
    public function getId() : int;

    /**
     * @throws Throwable
     */
    public static function await(mixed $await) : mixed;
}