<?php

namespace vennv;

use Fiber;
use Exception;
use FiberError;
use Throwable;

class EventQueue
{

    private const TIME_OUT = 5;

    private static int $nextId = 0;
    private static array $queues = [];
    private static array $returns = [];

    private static function generateId() : int
    {
        if (self::$nextId >= PHP_INT_MAX) 
        {
            self::$nextId = 0;
        }
        return self::$nextId++;
    }

    public static function getNextId() : int
    {
        if (self::$nextId >= PHP_INT_MAX)
        {
            self::$nextId = 0;
        }
        return self::$nextId + 1;
    }

    public static function isMaxId() : bool
    {
        return self::$nextId >= PHP_INT_MAX;
    }

    /**
     * @throws Throwable
     */
    public static function addQueue(Fiber $fiber, bool $isPromise = false, float $timeOut = 0.0) : int
    {
        $id = self::generateId();
        self::$queues[$id] = new Queue($id, $fiber, $timeOut, StatusQueue::PENDING, $isPromise);
        return $id;
    }

    public static function getQueue(int $id) : ?Queue
    {
        return self::$queues[$id] ?? null;
    }

    public static function getReturn(int $id) : mixed
    {
        return self::$returns[$id] ?? null;
    }

    public static function unsetReturn(int $id) : void
    {
        unset(self::$returns[$id]);
    }

    private static function getResultFiber(Fiber $fiber) : mixed
    {
        try
        {
            $result = $fiber->getReturn();
        }
        catch (FiberError $error)
        {
            $result = $error->getMessage();
        }
        return $result;
    }

    /**
     * @throws Throwable
     */
    private static function doResult(int $id) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $status = $queue->getStatus();
            $result = $queue->getReturn();

            switch ($status)
            {
                case StatusQueue::FULFILLED:
                    $queue->useCallableResolve($result);
                    break;
                case StatusQueue::REJECTED:
                    $queue->useCallableReject($result);
                    break;
                case StatusQueue::PENDING:
                    throw new EventQueueError("Queue with id $id is still pending.");
            }

            self::$returns[$id] = $queue;
            unset(self::$queues[$id]);
        }
        else
        {
            throw new EventQueueError("Queue with id $id not found.");
        }
    }

    /**
     * @throws Throwable
     */
    public static function runQueue(int $id) : void
    {
        $queue = self::getQueue($id);
        if ($queue !== null)
        {
            $fiber = $queue->getFiber();
            if (!$fiber->isStarted())
            {
                $fiber->start();
            }
        }
    }

    /**
     * @throws Throwable
     */
    private static function queueFulfilled(int $id) : bool
    {
        $queue = self::getQueue($id);
        if ($queue !== null)
        {
            $status = $queue->getStatus();
            return $status === StatusQueue::FULFILLED || $status === StatusQueue::REJECTED;
        }
        return false;
    }

    /**
     * @throws Throwable
     */
    public static function rejectQueue(int $id, mixed $result) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $queue->setStatus(StatusQueue::REJECTED);
            $queue->setReturn($result);
            self::doResult($id);
        }
    }

    /**
     * @throws Throwable
     */
    public static function fulfillQueue(int $id, mixed $result) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $queue->setStatus(StatusQueue::FULFILLED);
            $queue->setReturn($result);
            self::doResult($id);
        }
    }

    /**
     * @throws Throwable
     */
    public static function checkPromise(int $id, PromiseException $promise) : void
    {
        if ($promise->isRejected())
        {
            self::rejectQueue($id, $promise->getData());
        }
        else
        {
            self::fulfillQueue($id, $promise->getData());
        }
    }

    /**
     * @throws Throwable
     */
    private static function checkStatus(int $id) : void
    {
        $queue = self::getQueue($id);

        if (!is_null($queue))
        {
            $id = $queue->getId();
            $fiber = $queue->getFiber();
            $isPromise = $queue->isPromise();

            if (!$fiber->isStarted())
            {
                try
                {
                    $fiber->start();
                }
                catch (PromiseException $promise)
                {
                    self::checkPromise($id, $promise);
                }
                catch (Exception | Throwable $error)
                {
                    self::rejectQueue($id, $error);
                }
            }

            if ($fiber->isSuspended())
            {
                try
                {
                    $fiber->resume();
                }
                catch (PromiseException $promise)
                {
                    self::checkPromise($id, $promise);
                }
                catch (Exception | Throwable $error)
                {
                    self::rejectQueue($id, $error);
                }
            }
            elseif ($fiber->isTerminated())
            {
                if (!$isPromise)
                {
                    self::fulfillQueue($id, self::getResultFiber($fiber));
                }
            }
        }
        else
        {
            throw new EventQueueError("Queue with id $id not found.");
        }
    }

    /**
     * @throws Throwable
     */
    private static function run() : void
    {
        foreach (self::$queues as $id => $queue)
        {
            if ($queue instanceof Queue)
            {
                $timeOut = $queue->getTimeOut();
                $timeStart = $queue->getTimeStart();
                $timeNow = microtime(true);

                $diff = $timeNow - $timeStart;

                if ($diff >= $timeOut)
                {
                    self::checkStatus($id);
                }

                // If the queue is still pending after 5 seconds of timeout, reject it.
                if ($diff >= $timeOut + self::TIME_OUT)
                {
                    self::rejectQueue($id, "Queue with id $id timed out.");
                }
            }
        }
        foreach (self::$returns as $id => $queue)
        {
            $canDrop = $queue->canDrop();
            if ($canDrop)
            {
                unset(self::$returns[$id]);
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected static function runSingleJob() : void
    {
        while (count(self::$queues) > 0)
        {
            self::run();
        }
    }

    /**
     * @throws Throwable
     */
    protected static function runMultiJobs() : void
    {
        self::run();
    }

}