<?php

/*
 * Copyright (c) 2023 VennV
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv\vapm;

interface EventLoopInterface
{

    public static function generateId(): int;

    public static function addQueue(Promise $promise): void;

    public static function removeQueue(int $id): void;

    public static function getQueue(int $id): ?Promise;

    /**
     * @return array<int, Promise>
     * @phpstan-param array<int, Promise> $queues
     */
    public static function getQueues(): array;

    public static function addReturn(Promise $promise): void;

    public static function removeReturn(int $id): void;

    public static function getReturn(int $id): ?Promise;

    /**
     * @return array<int, Promise>
     * @phpstan-param array<int, Promise> $queues
     */
    public static function getReturns(): array;

}