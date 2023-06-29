<?php

namespace vennv;

final class Async {

    private static array $awaiting = [];
    private static array $listTerminated = [];

    public static function create(callable $callable) : mixed 
    {
        $fiber = new \Fiber($callable);
        $fiber->start();

        self::run();

        return $fiber->getReturn();
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

    public static function await(callable $callable) : mixed 
    {
        $fiber = new \Fiber($callable);

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

}