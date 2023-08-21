<?php

namespace vennv\api\simultaneous;

interface StatusThreadInterface
{
    /**
     * @return int|float
     *
     * This method is used to get the time sleeping.
     */
    public function getTimeSleeping(): int|float;

    /**
     * @return int|float
     *
     * This method is used to get the sleep start time.
     */
    public function getSleepStartTime(): int|float;

    /**
     * @param int|float $seconds
     *
     * This method is used to sleep the thread.
     */
    public function sleep(int|float $seconds): void;

    /**
     * @return bool
     *
     * This method is used to check if the thread can wake up.
     */
    public function canWakeUp(): bool;
}
