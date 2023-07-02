<?php

namespace vennv;

use Fiber;

final class Queue {

    private mixed $callableResolve;
    private mixed $callableReject;
    private bool $isResolved = false;

    public function __construct(
        private int $id,
        private Fiber $fiber,
        private float $timeOut, 
        private float $timeCurrent,
        private int $status,
        private mixed $return = null
    )
    {
        $this->callableResolve = function($result) {};
        $this->callableReject = function($result) {};
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

    public function getTimeCurrent() : float 
    {
        return $this->timeCurrent;
    }

    public function getStatus() : int 
    {
        return $this->status;
    }

    public function setStatus(int $status) : Queue 
    {
        $this->status = $status;
        return $this;
    }

    public function getReturn() : mixed 
    {
        return $this->return;
    }

    public function setReturn(mixed $return) : Queue 
    {
        $this->return = $return;
        return $this;
    }

    public function useCallableResolve(mixed $result) : void
    {
        ($this->callableResolve)($result);
    }

    public function setCallableResolve(mixed $callableResolve) : Queue
    {
        $this->callableResolve = $callableResolve;
        return $this;
    }

    public function useCallableReject(mixed $result) : void
    {
        ($this->callableReject)($result);
    }

    public function setCallableReject(mixed $callableReject) : Queue
    {
        $this->callableReject = $callableReject;
        return $this;
    }

    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    public function setResolved(bool $isResolved) : void
    {
        $this->isResolved = $isResolved;
    }

}