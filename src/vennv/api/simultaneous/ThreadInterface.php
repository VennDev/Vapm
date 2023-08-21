<?php

namespace vennv\api\simultaneous;

use Throwable;
use ReflectionException;
use vennv\vapm\simultaneous\Promise;
use vennv\vapm\simultaneous\DescriptorSpec;

interface ThreadInterface
{
    /**
     * This abstract method use to run the thread
     */
    public function onRun(): void;

    /**
     * @param array<int, array<string>> $mode
     *
     * @throws ReflectionException
     * @throws Throwable
     * @phpstan-param array<int, array<string>> $mode
     *
     * This method use to start the thread
     */
    public function start(array $mode = DescriptorSpec::BASIC): Promise;
}
