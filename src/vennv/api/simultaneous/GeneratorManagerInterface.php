<?php

namespace vennv\api\simultaneous;

interface GeneratorManagerInterface
{
    /**
     * @param int $milliseconds
     *
     * @return int
     *
     * This is a function that calculates the seconds from milliseconds for Generator vapm.
     * For example, if you run a function with multiple yields, this calculates the time spent on each of them in seconds.
     */
    public static function calculateSeconds(int $milliseconds): int;
}