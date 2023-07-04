<?php

namespace vennv;

use Fiber;
use Throwable;

final class Queue
{

    private const MAIN_QUEUE = "Main";

    private float $timeStart;
    private float $timeDrop;
    private mixed $return;
    private array $callableResolve;
    private array $callableReject;
    private mixed $returnResolve;
    private mixed $returnReject;
    private array $waitingPromises = [];

    public function __construct(
        private readonly int $id,
        private readonly Fiber $fiber,
        private readonly float $timeOut,
        private StatusQueue $status,
        private readonly bool $isPromise,
        private readonly bool $isPromiseAll = false
    )
    {
        $this->timeStart = microtime(true);
        $this->timeDrop = 15;
        $this->return = null;
        $this->callableResolve[self::MAIN_QUEUE] = function($result) {};
        $this->callableReject[self::MAIN_QUEUE] = function($result) {};
        $this->returnResolve = null;
        $this->returnReject = null;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getFiber() : Fiber
    {
        return $this->fiber;
    }

    public function getTimeOut() : float
    {
        return $this->timeOut;
    }

    public function getStatus() : StatusQueue
    {
        return $this->status;
    }

    public function setStatus(StatusQueue $status) : void
    {
        $this->status = $status;
    }

    public function isPromise() : bool
    {
        return $this->isPromise;
    }

    public function getTimeStart() : float
    {
        return $this->timeStart;
    }

    public function getReturn() : mixed
    {
        return $this->return;
    }

    public function setReturn(mixed $return) : void
    {
        $this->return = $return;
    }

    private function getResult(Fiber $fiber) : mixed
    {
        while (!$fiber->isTerminated())
        {
            if ($fiber->isTerminated())
            {
                break;
            }
        }

        return $fiber->getReturn();
    }

    private function checkStatus(string $callableFc, string $return) : void
    {
        while (count($this->{"$callableFc"}) > 0)
        {
            $firstCheck = false;
            $cancel = false;

            foreach ($this->{"$callableFc"} as $id => $callable)
            {
                if ($id !== self::MAIN_QUEUE && $this->{"$return"} instanceof Promise && !$firstCheck)
                {
                    EventQueue::getQueue($this->{"$return"}->getId())->setCallableResolve($callable);
                    $firstCheck = true;
                }
                elseif ($id !== self::MAIN_QUEUE)
                {
                    EventQueue::getQueue($this->{"$return"}->getId())->then($callable);
                    unset($this->{"$callableFc"}[$id]);
                    continue;
                }
                if (count($this->{"$callableFc"}) === 1)
                {
                    $cancel = true;
                }
            }

            if ($cancel)
            {
                break;
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function useCallableResolve(mixed $result) : void
    {
        if ($this->getStatus() === StatusQueue::FULFILLED)
        {

            $fiber = new Fiber(function() use ($result) {
                return ($this->callableResolve[self::MAIN_QUEUE])($result);
            });
            $fiber->start();

            unset($this->callableResolve[self::MAIN_QUEUE]);

            $this->returnResolve = $this->getResult($fiber);

            $this->checkStatus("callableResolve", "returnResolve");
        }
    }

    public function setCallableResolve(mixed $callableResolve) : Queue
    {
        $this->callableResolve[self::MAIN_QUEUE] = $callableResolve;
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function useCallableReject(mixed $result) : void
    {
        if ($this->getStatus() === StatusQueue::REJECTED)
        {
            $fiber = new Fiber(function () use ($result) {
                return ($this->callableReject[self::MAIN_QUEUE])($result);
            });
            $fiber->start();

            $this->returnReject = $this->getResult($fiber);

            $this->checkStatus("callableReject", "returnReject");
        }
    }

    public function setCallableReject(mixed $callableReject) : Queue
    {
        $this->callableReject[self::MAIN_QUEUE] = $callableReject;
        return $this;
    }

    public function getReturnResolve() : mixed
    {
        return $this->returnResolve;
    }

    public function getReturnReject() : mixed
    {
        return $this->returnReject;
    }

    public function thenPromise(callable $callable) : Queue
    {
        $this->setCallableResolve($callable);
        return $this;
    }

    public function catchPromise(callable $callable) : Queue
    {
        $this->setCallableReject($callable);
        return $this;
    }

    public function then(callable $callable) : Queue
    {
        $this->callableResolve[] = $callable;
        return $this;
    }

    public function catch(callable $callable) : Queue
    {
        $this->callableReject[] = $callable;
        return $this;
    }

    public function canDrop() : bool
    {
        return (microtime(true) - $this->timeStart) > $this->timeDrop;
    }

    public function getWaitingPromises() : array
    {
        return $this->waitingPromises;
    }

    public function setWaitingPromises(array $waitingPromises) : void
    {
        $this->waitingPromises = $waitingPromises;
    }

    public function isPromiseAll() : bool
    {
        return $this->isPromiseAll;
    }

    /**
     * @throws Throwable
     */
    public function hasCompletedAllPromise() : bool
    {
        $results = [];
        foreach ($this->waitingPromises as $value)
        {
            $result = $value;

            if (is_callable($value))
            {
                $fiber = new Fiber($value);
                $fiber->start();

                while (!$fiber->isTerminated())
                {
                    if ($fiber->isTerminated())
                    {
                        break;
                    }
                }

                $result = $fiber->getReturn();
            }

            if (
                $value instanceof Promise || $value instanceof Async ||
                $result instanceof Promise || $result instanceof Async
            )
            {
                $queue = EventQueue::getReturn($value->getId());

                if (!is_null($queue))
                {
                    if ($queue->getStatus() === StatusQueue::FULFILLED)
                    {
                        $results[] = $queue->getReturn();
                    }

                    if ($queue->getStatus() === StatusQueue::REJECTED)
                    {
                        throw new EventQueueError($queue->getReturn());
                    }
                }
            }
        }

        if (count($results) === count($this->waitingPromises))
        {
            $this->return = $results;
            $this->setStatus(StatusQueue::FULFILLED);
            return true;
        }

        return false;
    }

}