<?php

namespace vennv;

final class PromiseResult {

    public function __construct(
        private mixed $result,
        private bool $isResolved
    ) {}

    public static function forceGetResult(mixed $result) : mixed
    {
        while ($result instanceof PromiseResult) 
        {
            $result = $result->getResult();
            if ($result instanceof PromiseResult) 
            {
                $result = $result->getResult();
            } 
            else 
            {
                break;
            }
        }
        return $result;
    }

    public function getResult() : mixed
    {
        return self::forceGetResult(
            $this->result
        );
    }

    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    public function then(callable $callable) : PromiseResult
    {
        if ($this->isResolved) 
        {
            $callable(
                $this->result
            );
        }

        return $this;
    }

    public function catch(callable $callable) : PromiseResult
    {
        if (!$this->isResolved) 
        {
            $callable(
                $this->result
            );
        }

        return $this;
    }

}