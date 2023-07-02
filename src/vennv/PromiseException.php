<?php

namespace vennv;

use TypeError;

final class PromiseException extends TypeError {

    public function __construct(
        protected $data,
        protected $isResolved,
        protected $message,
        protected $code = 0
    ) {
        parent::__construct(
            $message,
            $code
        );
    }

    public function getData() : mixed
    {
        return $this->data;
    }

    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    public function __toString() : string
    {
        return __CLASS__ . ": [$this->code]: $this->message\n";
    }

}