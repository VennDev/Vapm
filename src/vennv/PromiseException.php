<?php

namespace vennv;

use TypeError;

final class PromiseException extends TypeError
{

    public function __construct(
        protected $data,
        protected $isRejected,
        protected $message,
        protected $code = 0
    )
    {
        parent::__construct(
            $message,
            $code
        );
    }

    public function getData() : mixed
    {
        return $this->data;
    }

    public function isRejected() : bool
    {
        return $this->isRejected;
    }

    public function __toString() : string
    {
        return __CLASS__ . ": [$this->code]: $this->message\n";
    }

}