<?php

namespace vennv;

use Fiber;
use Throwable;

final class Promise implements InterfacePromise
{

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callback)
    {
        $fiber = new Fiber($callback);

        $this->id = EventQueue::addQueue(
            $fiber,
            true
        );
    }

    public function then(callable $callable) : Queue
    {
        EventQueue::getQueue($this->id)->setCallableResolve($callable);
        return EventQueue::getQueue($this->id)->thenPromise($callable);
    }

    public function catch(callable $callable) : Queue
    {
        EventQueue::getQueue($this->id)->setCallableReject($callable);
        return EventQueue::getQueue($this->id)->catchPromise($callable);
    }

    public static function resolve(mixed $result) : void
    {
        throw new PromiseException(
            $result,
            false,
            "resolved"
        );
    }

    public static function reject(mixed $result) : void
    {
        throw new PromiseException(
            $result,
            true,
            "rejected"
        );
    }

    /**
     * @throws Throwable
     */
    public static function all(array $promises) : Async
    {
        return new Async(function() use ($promises) {
            $results = [];
            foreach ($promises as $value)
            {
                $result = $value;

                if (is_callable($value))
                {
                    $fiber = new Fiber($value);
                    $fiber->start();

                    if (!$fiber->isTerminated())
                    {
                        Async::wait();
                    }

                    $result = $fiber->getReturn();
                }

                if ($value instanceof Promise || $value instanceof Async)
                {
                    $queue = EventQueue::getReturn($value->getId());

                    if (!is_null($queue))
                    {
                        if ($queue->getStatus() === StatusQueue::PENDING)
                        {
                            Async::wait();
                        }

                        $result = $queue->getReturn();
                    }
                    else
                    {
                        Async::wait();
                    }
                }

                if ($result instanceof Promise || $result instanceof Async)
                {
                    $queue = EventQueue::getReturn($value->getId());

                    if (!is_null($queue))
                    {
                        if ($queue->getStatus() === StatusQueue::PENDING)
                        {
                            Async::wait();
                        }

                        $result = $queue->getReturn();
                    }
                    else
                    {
                        Async::wait();
                    }
                }

                $results[] = $result;
            }
            return $results;
        });
    }

    public function getId() : int
    {
        return $this->id;
    }

}