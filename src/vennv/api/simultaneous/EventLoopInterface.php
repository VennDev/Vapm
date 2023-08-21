<?php

namespace vennv\api\simultaneous;

use vennv\vapm\simultaneous\Promise;

interface EventLoopInterface
{
    public static function generateId(): int;

    public static function addQueue(Promise $promise): void;

    public static function removeQueue(int $id): void;

    public static function getQueue(int $id): ?Promise;

    public static function addReturn(Promise $promise): void;

    public static function removeReturn(int $id): void;

    public static function getReturn(int $id): ?Promise;

    /**
     * @return array<int, Promise>
     */
    public static function getQueues(): array;

    /**
     * @return array<int, Promise>
     */
    public static function getReturns(): array;
}
