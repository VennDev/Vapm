<?php

namespace vennv;

final class PromiseResult {

    public function __construct(
        private bool $isReject,
        private mixed $result
    ) {}

    public function getResult() : mixed 
    {
        return $this->result;
    }

    private function isReject() : bool 
    {
        return $this->isReject;
    }

    public function reject(mixed $result) : Promise 
    {
        $this->isReject = true;
        $this->result = $result;
        return $this->result;
    }

    public function then(callable $callable) : PromiseResult
    {
        if (!self::isReject()) 
        {
            $callable($this->result);
        } 

        return new PromiseResult($this->isReject, $this->result);
    }

    public function catch(callable $callable) : PromiseResult
    {
        if (self::isReject()) 
        {
            $callable($this->result);
        } 
        
        return new PromiseResult($this->isReject, $this->result);
    }

}