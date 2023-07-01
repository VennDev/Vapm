<?php

namespace vennv;

final class LoopException extends \Exception {

    public function __construct(
        private string $message,
        private int $code = 0,
        private ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message, 
            $code, 
            $previous
        );
    }

    public function __toString() : string 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}