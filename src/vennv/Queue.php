<?php

namespace vennv;

use Fiber;
use Throwable;

final class Queue
{

    private const MAIN_QUEUE = "Main";

    private float $timeStart;
    private mixed $return;
    private array $callableResolve;
    private array $callableReject;
    private mixed $returnResolve;
    private mixed $returnReject;

    public function __construct(
        private readonly int $id,
        private readonly Fiber $fiber,
        private readonly float $timeOut,
        private StatusQueue $status,
        private readonly bool $isPromise
    )
    {
        $this->timeStart = microtime(true);
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

            while (count($this->callableResolve) > 0)
            {
                $firstCheck = false;
                $cancel = false;

                foreach ($this->callableResolve as $id => $callable)
                {
                    if ($id !== self::MAIN_QUEUE && $this->returnResolve instanceof Promise && !$firstCheck)
                    {
                        EventQueue::getQueue($this->returnResolve->getId())->setCallableResolve($callable);
                        $firstCheck = true;
                    }
                    elseif ($id !== self::MAIN_QUEUE)
                    {
                        EventQueue::getQueue($this->returnResolve->getId())->then($callable);
                        unset($this->callableResolve[$id]);
                        continue;
                    }
                    if (count($this->callableResolve) === 1)
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

            while (count($this->callableReject) > 0)
            {
                $cancel = false;
                foreach ($this->callableReject as $id => $callable)
                {
                    if ($id !== self::MAIN_QUEUE && $this->returnReject instanceof Promise)
                    {
                        EventQueue::getQueue($this->returnReject->getId())->setCallableReject($callable);
                        $this->returnReject = null;
                    }
                    elseif ($id !== self::MAIN_QUEUE)
                    {
                        EventQueue::getQueue($this->returnReject->getId())->then($callable);
                    }
                    else
                    {
                        $cancel = true;
                    }
                    unset($this->callableReject[$id]);
                }

                if ($cancel)
                {
                    break;
                }
            }
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

}