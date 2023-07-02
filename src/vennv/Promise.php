<?php

namespace vennv;

final class Promise implements InterfacePromise {

    private int $id;

    public function __construct(callable $callback)
    {
        $this->id = EventQueue::add(
            $callback,
            Timer::MINIMUM_WORKING_TIME
        );
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function then(callable $callable) : Promise
    {
        EventQueue::get(
            $this->id
        )->setCallableResolve(
            $callable
        );

        return $this;
    }

    public function catch(callable $callable) : Promise
    {
        EventQueue::get(
            $this->id
        )->setCallableReject(
            $callable
        );

        return $this;
    }

    public static function resolve(mixed $result) : void
    {
        throw new PromiseException(
            $result,
            true,
            "resolve"
        );
    }

    public static function reject(mixed $result) : void
    {
        throw new PromiseException(
            $result,
            false,
            "reject"
        );
    }

}