<?php

namespace vennv\api\simultaneous;

use Generator;
use vennv\vapm\simultaneous\ChildCoroutine;

interface DeferredInterface
{
    /**
     * This method is used to get the result of the deferred.
     */
    public function await(): Generator;

    /**
     * @param DeferredInterface ...$deferreds
     *
     * @return Generator
     *
     * This method is used to get the result of the deferred.
     */
    public static function awaitAll(DeferredInterface ...$deferreds): Generator;

    /**
     * This method is used to get the child coroutine of the deferred.
     */
    public function getChildCoroutine(): ChildCoroutine;

    /**
     * This method is used to get the result of the deferred.
     */
    public function getComplete(): mixed;
}
