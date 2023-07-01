<?php

namespace vennv;

use Fiber;
use Throwable;

final class Async {

    private int $id;

    /**
     * @param callable $callable
     * 
     * This function is used to create a new async function
     */
    public function __construct(callable $callable) 
    {
        $this->id = EventQueue::add(
            $callable, 
            Timer::MINIMUM_WORKING_TIME
        );
    }

    /**
     * @param Async|callable $callable $callable
     *
     * This function is used to create a new async await function
     * You should use this function in an async function
     *
     * @return mixed
     * @throws Throwable
     */
    public static function await(Async|callable $callable) : mixed 
    {
        if ($callable instanceof Async) 
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

    /**
     * @return int
     * 
     * This function is used to get the id of the async function
     */
    public function getId() : int 
    {
        return $this->id;
    }

}