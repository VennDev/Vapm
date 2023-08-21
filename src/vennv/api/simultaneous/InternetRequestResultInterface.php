<?php

namespace vennv\api\simultaneous;

interface InternetRequestResultInterface
{
    /**
     * @return string[][]
     */
    public function getHeaders(): array;

    public function getBody(): string;

    public function getCode(): int;
}
