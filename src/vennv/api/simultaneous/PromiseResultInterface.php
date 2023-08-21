<?php

namespace vennv\api\simultaneous;

use vennv\vapm\enums\StatusPromise;

interface PromiseResultInterface
{
    public function getStatus(): StatusPromise;

    public function getResult(): mixed;
}