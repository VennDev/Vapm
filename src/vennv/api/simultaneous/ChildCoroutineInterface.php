<?php

namespace vennv\api\simultaneous;

use Exception;

interface ChildCoroutineInterface
{
    public function setException(Exception $exception): void;

    public function run(): void;

    public function isFinished(): bool;

    public function getReturn(): mixed;
}
