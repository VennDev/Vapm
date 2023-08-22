<?php

namespace vennv\api\simultaneous;

use vennv\vapm\simultaneous\enums\StatusPromise;

interface PromiseResultInterface
{
    public function getStatus(): StatusPromise;

    public function getResult(): mixed;
}