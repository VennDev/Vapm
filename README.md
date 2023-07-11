# Vapm
- A library Async & Promise for PHP
- The method is based on Fibers, requires you to have php version from >= 8.1

# Clarify
- The library is based on a stop-and-stop mechanism with fiber that makes tasks such as reading files or fetching results from a website non-blocking mechanisms.
- As explained by PHP:

Fibers represent full-stack, interruptible functions. Fibers may be suspended from anywhere in the call-stack, pausing execution within the fiber until the fiber is resumed at a later time.

Fibers pause the entire execution stack, so the direct caller of the function does not need to change how it invokes the function.

Execution may be interrupted anywhere in the call stack using Fiber::suspend() (that is, the call to Fiber::suspend() may be in a deeply nested function or not even exist at all).

Unlike stack-less Generators, each Fiber has its own call stack, allowing them to be paused within deeply nested function calls. A function declaring an interruption point (that is, calling Fiber::suspend()) need not change its return type, unlike a function using yield which must return a Generator instance.

Fibers can be suspended in any function call, including those called from within the PHP VM, such as functions provided to array_map() or methods called by foreach on an Iterator object.

Once suspended, execution of the fiber may be resumed with any value using Fiber::resume() or by throwing an exception into the fiber using Fiber::throw(). The value is returned (or exception thrown) from Fiber::suspend().

- Shows are: ``Shows that the pause and continuation of Fiber is very effective. Because each Fiber has its own call stack.``
- It's clear that fibers are a significant improvement, both syntax-wise and in flexibility.

# Next update ?
- Simply add some other asynchronous features so that this library is as similar to Javascript as possible.
# How to use System?
```php
    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with multiple event loops
     */
    public static function runEventLoop(): void;

    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with single event loop
     */
    public static function runSingleEventLoop(): void;

    /**
     * This function is used to run a callback in the event loop with timeout
     */
    public static function setTimeout(callable $callback, int $timeout): SampleMacro;

    /**
     * This function is used to clear the timeout
     */
    public static function clearTimeout(SampleMacro $sampleMacro): void;

    /**
     * This function is used to run a callback in the event loop with interval
     */
    public static function setInterval(callable $callback, int $interval): SampleMacro;

    /**
     * This function is used to clear the interval
     */
    public static function clearInterval(SampleMacro $sampleMacro): void;

    /**
     * @param string $url
     * @param array<string|null, string|array> $options
     * @return Promise when Promise resolve InternetRequestResult and when Promise reject Error
     * @throws Throwable
     * @phpstan-param array{method?: string, headers?: array<int, string>, timeout?: int, body?: array<string, string>} $options
     */
    public static function fetch(string $url, array $options = []) : Promise;

    /**
     * @throws Throwable
     *
     * This is a function used only to retrieve results from an address or file path via the file_get_contents method
     */
    public static function read(string $path) : Promise;   
```
# How to use Async?
```php
    public function getId(): int;

    /**
     * @throws Throwable
     */
    public static function await(Promise|Async|callable $await): mixed;
```
# How to use Promise?
```php
    public function getId(): int;

    public function getFiber(): Fiber;

    public function isJustGetResult(): bool;

    public function getTimeOut(): float;

    public function getTimeStart(): float;

    public function getTimeEnd(): float;

    public function setTimeEnd(float $timeEnd): void;

    public function canDrop(): bool;

    public function getStatus(): string;

    public function isPending(): bool;

    public function isResolved(): bool;

    public function isRejected(): bool;

    public function getResult(): mixed;

    public function getReturn(): mixed;

    public function getCallback(): callable;

    public function resolve(mixed $value): void;

    public function reject(mixed $value): void;

    public function then(callable $callback): Promise;

    public function catch(callable $callback): Promise;

    public function finally(callable $callback): Promise;

    /**
     * @throws Throwable
     */
    public function useCallbacks(): void;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function all(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function allSettled(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function any(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function race(array $promises): Promise;
```
# Examples:
- Async:
```php
function testA() {
    sleep(3);
    return 1;
}

function testB() {
    new Async(function () {
        var_dump("AAA");
        $result = Async::await(testA());
        var_dump($result);
        $result = Async::await(testA());
        var_dump($result);
    });
}

testB();

System::runSingleEventLoop();
```
- Promise:
```php
function testA() {
    return new Promise(function($resolve, $reject) {
        $resolve("Hello World");
    });
}

function testB() {
    new Async(function () {
        $result = Async::await(testA());
        var_dump($result);
    });
}

testB();

System::runSingleEventLoop();
```
- Chaining Promises:
```php
function testPromise1() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("A");
        }, 1000);
    });
}

function testPromise2() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("B");
        }, 1000);
    });
}

function testPromise3() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("C");
        }, 1000);
    });
}

function testPromise4() : Promise {
    return new Promise(function ($resolve, $reject) {
        System::setTimeout(function () use ($resolve) {
            $resolve("D");
        }, 1000);
    });
}

testPromise1()->then(function ($value) {
    var_dump($value);
    return testPromise2();
})->then(function ($value) {
    var_dump($value);
    return testPromise3();
})->then(function ($value) {
    var_dump($value);
    return testPromise4();
})->then(function ($value) {
    var_dump($value);
})->catch(function ($value) {
    var_dump($value);
})->finally(function() {
    var_dump("Complete!");
});

System::runSingleEventLoop();
```
- ``Promise::all()`` Function:
```php
function promise1() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise1");
        }, 5000);
    });
}

function promise2() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise2");
        }, 3000);
    });
}

function promise3() : Promise {
    return new Promise(function($resolve, $reject) {
        System::setTimeout(function() use ($resolve) {
            $resolve("promise3");
        }, 3000);
    });
}

function asyncTest() {
    new Async(function() {
		$time = microtime(true);
        $promise = Async::await(Promise::all([
            promise1(),
            promise2(),
            promise3()
        ]));
		var_dump(microtime(true) - $time);
        var_dump($promise);
    });
}

asyncTest();

System::runSingleEventLoop();
```
- ``setTimeout`` Function:
```php
function testAsync() {
    System::setTimeout(function() {
        echo "Hello World\n";
    }, 5000);
    var_dump("Hello");
}

testAsync();
System::endSingleJob();
```
- ``setInterval`` Function:
```php
function asyncTest() {
    System::setInterval(function() {
        var_dump("Hello World!");
    }, 1000);
}

asyncTest();

System::runSingleEventLoop();
```
- ``Fetch & Read`` Function:
```php
$url = "https://www.google.com/";

System::fetch($url)->then(function($value) {
    var_dump($value);
})->catch(function($reason) {
    var_dump($reason);
});

System::runSingleEventLoop();
```
```php
$url = "https://www.google.com/";

System::read($url)->then(function($value) {
    var_dump($value);
})->catch(function($reason) {
    var_dump($reason);
});

System::runSingleEventLoop();
```
