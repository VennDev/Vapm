<?php

namespace vennv;

use Fiber;
use Throwable;

final class EventQueue {

    public const PENDING = 0;
    public const RUNNING = 1;
    public const FINISHED = 2;

    public const IS_NOT_TIMEOUT = 0;
    public const IS_TIMEOUT = 1;

    private static int $nextId = 0;
    private static array $queues = [];
    private static array $returns = [];

    public static function generateId() : int 
    {
        if (self::$nextId >= PHP_INT_MAX) 
        {
            self::$nextId = 0;
        }
        return self::$nextId++;
    }

    public static function add(callable $callable, float $time) : int 
    {
        $id = self::generateId();
            
        $timeOut = Utils::milliSecsToSecs(
            $time
        );

        $fiber = new Fiber(
            $callable
        );

        self::$queues[$id] = new Queue(
            $id, 
            $fiber, 
            $timeOut, 
            microtime(true), 
            self::PENDING
        );

        return $id;
    }

    public static function remove(int $id) : bool 
    {
        if (isset(self::$queues[$id])) 
        {
            self::$returns[$id] = self::$queues[$id];
            unset(self::$queues[$id]);
            return true;
        }
        return false;
    }

    public static function get(int $id) : ?Queue 
    {
        if (isset(self::$queues[$id])) 
        {
            return self::$queues[$id];
        }
        return null;
    }

    public static function doResult(int $id) : ?Queue 
    {
        if (isset(self::$returns[$id])) 
        {
            $result = clone self::$returns[$id];
            unset(self::$returns[$id]);
            return $result;
        }
        return null;
    }

    /**
     * @throws Throwable
     */
    public static function runById(int $id) : void
    {
        while (!is_null(self::get($id))) 
        {
            $childLoop = self::get($id);

            if ($childLoop instanceof Queue) 
            {

                $fiber = $childLoop->getFiber();
                $timeCurrent = $childLoop->getTimeCurrent();
                $timeOut = $childLoop->getTimeOut();

                $diff = microtime(true) - $timeCurrent;

                if ($diff >= $timeOut && $childLoop->getStatus() === self::PENDING) 
                {

                    self::$queues[$id] = $childLoop->setStatus(
                        self::RUNNING
                    );

                    $fiber->start();
                }

                if ($childLoop->getStatus() === self::RUNNING) 
                {
                    while (!$fiber->isTerminated()) 
                    {
                        if ($fiber->isSuspended()) 
                        {
                            $fiber->resume();
                        }

                        if ($fiber->isTerminated()) 
                        {
                            break;
                        }
                    }

                    if ($fiber->isTerminated()) 
                    {
                        self::$queues[$id] = $childLoop->setStatus(
                            self::FINISHED
                        );
                    }
                }

                if ($childLoop->getStatus() === self::FINISHED) 
                {
                    self::$queues[$id] = $childLoop->setReturn(
                        $fiber->getReturn()
                    );

                    if (!self::remove($id)) {
                        try {
                            throw new EventQueueException(
                                "Error removing loop with id: $id",
                                0,
                            );
                        } catch (EventQueueException $e) {
                            echo $e;
                        }
                    }
                }

                // If the loop is running for more than 5 seconds, it will be stopped.
                if ($diff >= $timeOut + 5) 
                {
                    self::$queues[$id] = $childLoop->setReturn(
                        $fiber->getReturn()
                    );

                    if (!self::remove($id)) {
                        try {
                            throw new EventQueueException(
                                "Error removing loop with id: $id, in timeout",
                                0,
                            );
                        } catch (EventQueueException $e) {
                            echo $e;
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws Throwable
     */
    public static function run(int $mode = self::IS_NOT_TIMEOUT) : void
    {

        while (count(self::$queues) > 0) 
        {
            foreach (self::$queues as $id => $queue) 
            {

                if ($queue instanceof Queue) 
                {

                    $fiber = $queue->getFiber();
                    $timeCurrent = $queue->getTimeCurrent();
                    $timeOut = $queue->getTimeOut();

                    $diff = microtime(true) - $timeCurrent;

                    if ($diff >= $timeOut && $queue->getStatus() === self::PENDING) 
                    {

                        self::$queues[$id] = $queue->setStatus(
                            self::RUNNING
                        );

                        $fiber->start();
                    }
                    elseif ($queue->getStatus() === self::PENDING) 
                    {
                        continue;
                    }

                    if ($queue->getStatus() === self::RUNNING) 
                    {
                        while (!$fiber->isTerminated()) 
                        {
                            if ($fiber->isSuspended()) 
                            {
                                $fiber->resume();
                            }

                            if ($fiber->isTerminated()) 
                            {
                                break;
                            }
                        }

                        if ($fiber->isTerminated()) 
                        {
                            self::$queues[$id] = $queue->setStatus(
                                self::FINISHED
                            );
                        }
                    }

                    if ($queue->getStatus() === self::FINISHED) 
                    {
                        self::$queues[$id] = $queue->setReturn(
                            $fiber->getReturn()
                        );

                        if (!self::remove($id)) {
                            try {
                                throw new EventQueueException(
                                    "Error removing loop with id: $id",
                                    0,
                                );
                            } catch (EventQueueException $e) {
                                echo $e;
                            }
                        }

                        continue;
                    }

                    // If the loop is running for more than 5 seconds, it will be stopped.
                    if ($diff >= $timeOut + 5) 
                    {
                        self::$queues[$id] = $queue->setReturn(
                            $fiber->getReturn()
                        );

                        if (!self::remove($id)) {
                            try {
                                throw new EventQueueException(
                                    "Error removing loop with id: $id, in timeout",
                                    0,
                                );
                            } catch (EventQueueException $e) {
                                echo $e;
                            }
                        }
                    }
                }
            }

            if ($mode === self::IS_NOT_TIMEOUT) 
            {
                break;
            }
        }
    }

}