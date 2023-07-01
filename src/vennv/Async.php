<?php

namespace vennv;

final class Async {

    public static function create(callable $callable) : AsyncResult 
    {
        if ($callable instanceof AsyncResult) 
        {
            new LoopException(
                "Async::async() does not accept a async as parameter", 
                1
            );
        }

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

        return new AsyncResult(
            $fiber->getReturn()
        );
    }

    public static function await(AsyncResult|callable $callable) : mixed 
    {

        if ($callable instanceof AsyncResult) 
        {
            return $callable;
        }

        $loop = Loop::add(
            $callable, 
            0
        );

        $status = Loop::get(
            $loop
        )->getStatus();

        while ($status !== Loop::FINISHED) 
        {
            $dataLoop = Loop::get(
                $loop
            );

            if ($dataLoop == null) 
            {
                break;
            }

            $status = $dataLoop->getStatus();

            if ($status === Loop::FINISHED) 
            {
                break;
            }

            Loop::run();
        }

        $result = Loop::doResult(
            $loop
        )->getReturn();

        if ($result instanceof ChildLoop) 
        {
            return $result->getReturn();
        }

        return $result;
    }

}