<?php

namespace vennv\api\simultaneous;

interface CoroutineThreadInterface
{
    /**
     * @return void
     *
     * This function runs the callback function for the thread.
     */
    public function onRun(): void;
}
