<?php

namespace vennv;

final class Promise {

    private static bool $isReject = false;
    private static array $awaiting = [];
    private static array $listTerminated = [];
    private static mixed $result = null;

    public function __construct(callable $callable) 
    {
        
        try {

            $fiber = new \Fiber(function () use ($callable) {
                return self::await($callable);
            });
            $fiber->start();

            self::$result = $fiber->getReturn();
        } 
        catch (\Throwable | \Exception $error) 
        {
            self::$result = $error;
        }

        self::run();
    }

    public static function getResult() : mixed 
    {
        return self::$result;
    }

    private static function isReject() : bool 
    {
        return self::$isReject;
    }

    public static function reject(mixed $result) : PromiseResult 
    {
        self::$isReject = true;
        self::$result = $result;
        return new PromiseResult(self::$isReject, self::$result);
    }

    public static function resolve(mixed $result) : PromiseResult 
    {
        self::$isReject = false;
        self::$result = $result;
        return new PromiseResult(self::$isReject, self::$result);
    }

    private static function addWait(ChildAwait $await) : bool 
    {
        try 
        {
            self::$awaiting[] = $await;
            return true;
        } 
        catch (\Throwable $e) 
        {
            return false;
        }
    }

    private static function dropAwaits() : bool 
    {
        try 
        {
            foreach (self::$listTerminated as $index) 
            {
                unset(self::$awaiting[$index]);
            }
            return true;
        } 
        catch (\Throwable $e) 
        {
            return false;
        }
    }

    private static function addTerminated(int $index) : bool 
    {
        try 
        {
            self::$listTerminated[] = $index;
            return true;
        } 
        catch (\Throwable $e) 
        {
            return false;
        }
    }

    private static function processFiber(?\Fiber $fiber, int $index) : bool 
    {
        if (!is_null($fiber)) 
        {
            if ($fiber->isSuspended() && !$fiber->isTerminated()) 
            {
                $fiber->resume();
            } 
            elseif ($fiber->isTerminated()) 
            {
                self::addTerminated($index);
            }
            return true;
        }
        return false;
    }

    private static function await(mixed $input) : mixed 
    {
        if (is_callable($input)) 
        {
            $fiber = new \Fiber($input);
        } 
        else 
        {
            $fiber = new \Fiber(fn() => $input);
        }

        $await = new ChildAwait(
            \Fiber::getCurrent(),
            $fiber
        );
        
        self::addWait($await);

        $fiber->start();

        while (!$fiber->isTerminated()) 
        {

            $fiber->resume();

            if (!$fiber->isTerminated()) 
            {
                \Fiber::suspend();
            } 
            else 
            {
                break;
            }
        }

        return $fiber->getReturn();
    }

    private static function run() : void 
    {
        while (count(self::$awaiting) > 0) 
        {
            foreach (self::$awaiting as $index => $data) 
            {
                $parent = $data->getCurrent();
                $fiber = $data->getFiber();

                if (!self::processFiber($parent, $index)) 
                {
                    self::processFiber($fiber, $index);
                }
            }
            self::dropAwaits();
            self::$awaiting = array_values(self::$awaiting);
        }
    }

    public static function all(array $await) : PromiseResult
    {
        $result = [];
        foreach ($await as $value) 
        {
            if ($value instanceof Promise) 
            {
                $result[] = $value->getResult();
            }
            elseif (is_callable($value)) 
            {
                $result[] = self::await($value);
            }
            else 
            {
                $result[] = self::await($value);
            }
        }
        return new PromiseResult(self::$isReject, $result);
    }

    public function then(callable $callable) : PromiseResult
    {
        if (!self::isReject()) 
        {
            $callable(self::$result);
        } 

        return new PromiseResult(self::$isReject, self::$result);
    }

    public function catch(callable $callable) : PromiseResult
    {
        if (self::isReject()) 
        {
            $callable(self::$result);
        } 

        return new PromiseResult(self::$isReject, self::$result);
    }

}