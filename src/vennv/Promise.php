<?php

namespace vennv;

final class Promise {

    private bool $isResolved = false;
    private ?PromiseResult $result = null;

    public function __construct(callable $callable) 
    {

        if ($callable instanceof AsyncResult) 
        {
            new PromiseException(
                " Promise construct does not accept a async as parameter",
                1
            );
        }

        if ($callable instanceof PromiseResult) 
        {
            $this->result = $callable;
        }
        else 
        {
            try 
            {

                $fiber = new \Fiber(
                    $callable
                );

                $fiber->start();

                while (!$fiber->isTerminated()) 
                {
                    if ($fiber->isSuspended()) 
                    {
                        $fiber->resume();
                    }

                    if (!$fiber->isTerminated()) 
                    {
                        \Fiber::suspend();
                    } 
                    else 
                    {
                        break;
                    }
                }

                $this->result = new PromiseResult(
                    $fiber->getReturn(),
                    true
                );
            } 
            catch (\Throwable | \Exception $error) 
            {
                $this->result = new PromiseResult(
                    $error,
                    false
                );
            }
        }
    }

    public static function all(array $promises) : PromiseResult
    {
        $results = [];

        foreach ($promises as $promise) 
        {
            if ($promise instanceof Promise) 
            {
                $results[] = $promise->getResult();
            }
            elseif ($promise instanceof PromiseResult) 
            {
                $results[] = PromiseResult::forceGetResult(
                    $promise
                );
            }
            elseif ($promise instanceof \Fiber) 
            {
                while (!$promise->isTerminated()) 
                {
                    if ($promise->isSuspended()) 
                    {
                        $promise->resume();
                    }

                    if (!$promise->isTerminated()) 
                    {
                        \Fiber::suspend();
                    } 
                    else 
                    {
                        break;
                    }
                }
                $results[] = $promise->getReturn();
            }
            elseif ($promise instanceof AsyncResult) 
            {
                $results[] = $promise->getResult();
            }
            else
            {
                $results[] = $promise;
            }
        }

        return new PromiseResult(
            $results,
            true
        );
    }

    public static function resolve(mixed $result) : PromiseResult
    {
        return new PromiseResult(
            $result,
            true
        );
    }

    public static function reject(mixed $error) : PromiseResult
    {
        return new PromiseResult(
            $error,
            true
        );
    }

    public function then(callable $callable) : PromiseResult
    {
        return $this->result->then(
            $callable
        );
    }

    public function catch(callable $callable) : PromiseResult
    {
        return $this->result->catch(
            $callable
        );
    }

    public function getResult() : mixed
    {
        return PromiseResult::forceGetResult(
            $this->result
        );
    }

}