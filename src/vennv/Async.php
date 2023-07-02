<?php

namespace vennv;

use Fiber;
use Throwable;

final class Async implements InterfaceAsync {

    private int $id;

    /**
     * @param callable $callable
     * 
     * This function is used to create a new async function
     */
    public function __construct(callable $callable) 
    {
        $this->id = EventQueue::add(
            callable: $callable,
            time: Timer::MINIMUM_WORKING_TIME,
            inPromise: false
        );
    }

    /**
     * @throws Throwable
     */
    public static function await(Promise|Async|callable $callable) : mixed
    {
        if ($callable instanceof Async || $callable instanceof Promise)
        {

            $async = $callable;

            $status = EventQueue::get(
                $async->getId()
            )->getStatus();

            while ($status !== EventQueue::FINISHED) 
            {
                $dataQueue = EventQueue::get(
                    $async->getId()
                );

                if ($dataQueue == null) 
                {
                    break;
                }

                $status = $dataQueue->getStatus();

                if ($status === EventQueue::FINISHED) 
                {
                    break;
                }

                EventQueue::runById($async->getId());
            }

            $result = EventQueue::doResult(
                $async->getId()
            )->getReturn();

            if ($result instanceof Queue) 
            {
                return $result->getReturn();
            }

            return $result;
        }

        if ($callable instanceof AsyncResult) 
        {
            new AsyncException(
                "Async::await() does not accept a async as parameter", 
                1
            );
        }

        $fiber = new Fiber(
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
                Fiber::suspend();
            } 
            else 
            {
                break;
            }
        }

        return $fiber->getReturn();
    }

    public function getId() : int 
    {
        return $this->id;
    }

}