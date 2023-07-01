<?php

namespace vennv;

final class AsyncResult {

    public function __construct(
        private mixed $result,
    ) {}

    public function getResult() : mixed 
    {
        return $this->result;
    }

}