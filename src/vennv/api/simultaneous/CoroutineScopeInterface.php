<?php

namespace vennv\api\simultaneous;

use Throwable;
use ReflectionException;

interface CoroutineScopeInterface
{
    /**
     * @return bool
     *
     * This function checks if the coroutine has finished.
     */
    public function isFinished(): bool;

    /**
     * @return bool
     *
     * This function checks if the coroutine has been cancelled.
     */
    public function isCancelled(): bool;

    /**
     * This function cancels the coroutine.
     */
    public function cancel(): void;

    /**
     * @param mixed ...$callbacks
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * This function launches a coroutine.
     */
    public function launch(mixed ...$callbacks): void;

    /**
     * This function runs the coroutine.
     */
    public function run(): void;
}
