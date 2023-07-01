<?php

namespace vennv;

final class Loop {

    public const PENDING = 0;
    public const RUNNING = 1;
    public const FINISHED = 2;
    public const IS_REPEAT = 3;

    public const IS_NOT_TIMEOUT = 0;
    public const IS_TIMEOUT = 1;

    private static int $nextId = 0;
    private static array $loop = [];
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
            
        $timeOut = Utils::miliSecsToSecs(
            $time
        );

        $fiber = new \Fiber(
            $callable
        );

        self::$loop[$id] = new ChildLoop(
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
        if (isset(self::$loop[$id])) 
        {
            self::$returns[$id] = self::$loop[$id];
            unset(self::$loop[$id]);
            return true;
        }
        return false;
    }

    public static function get(int $id) : ?ChildLoop 
    {
        if (isset(self::$loop[$id])) 
        {
            return self::$loop[$id];
        }
        return null;
    }

    public static function doResult(int $id) : ?ChildLoop 
    {
        if (isset(self::$returns[$id])) 
        {
            $result = clone self::$returns[$id];
            unset(self::$returns[$id]);
            return $result;
        }
        return null;
    }

    public static function run(int $mode = self::IS_NOT_TIMEOUT) : void 
    {

        while (count(self::$loop) > 0) 
        {
            foreach (self::$loop as $id => $childLoop) 
            {

                if ($childLoop instanceof ChildLoop) 
                {

                    $fiber = $childLoop->getFiber();
                    $timeCurrent = $childLoop->getTimeCurrent();
                    $timeOut = $childLoop->getTimeOut();

                    $diff = microtime(true) - $timeCurrent;

                    if ($diff >= $timeOut && $childLoop->getStatus() === self::PENDING) 
                    {

                        self::$loop[$id] = $childLoop->setStatus(
                            self::RUNNING
                        );

                        $fiber->start();
                    }
                    elseif ($childLoop->getStatus() === self::PENDING) 
                    {
                        continue;
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
                            self::$loop[$id] = $childLoop->setStatus(
                                self::FINISHED
                            );
                        }
                    }

                    if ($childLoop->getStatus() === self::FINISHED) 
                    {
                        self::$loop[$id] = $childLoop->setReturn(
                            $fiber->getReturn()
                        );

                        if (!self::remove($id)) {
                            try {
                                throw new LoopException(
                                    "Error removing loop with id: {$id}", 
                                    0,
                                );
                            } catch (LoopException $e) {
                                echo $e;
                            }
                        }

                        continue;
                    }

                    // If the loop is running for more than 5 seconds, it will be stopped.
                    if ($diff >= $timeOut + 5) 
                    {
                        self::$loop[$id] = $childLoop->setReturn(
                            $fiber->getReturn()
                        );

                        if (!self::remove($id)) {
                            try {
                                throw new LoopException(
                                    "Error removing loop with id: {$id}, in timeout",
                                    0,
                                );
                            } catch (LoopException $e) {
                                echo $e;
                            }
                        }

                        continue;
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