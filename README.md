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
     * This method is used to run callback after a certain amount of time.
     */
    public static function setTimeout(callable $callable, int $timeout) : void;

    /**
     * @throws Throwable
     *
     * This method is used to run repeatable callback after a certain amount of time.
     */
    public static function setInterval(callable $callable, int $interval) : void;

    /**
     * @param array<int, mixed> $options
     *
     * This method is used to fetch data from an url.
     */
    public static function fetch(string $url, array $options = [CURLOPT_RETURNTRANSFER => true]) : Promise;

    /**
     * This method is used to fetch data from an url. But it uses file_get_contents() instead of curl.
     */
    public static function fetchJg(string $url) : Promise;

    /**
     * @throws Throwable
     *
     * This method is used to run a single job.
     * It is used when you want to run the event loop in a blocking way.
     */
    public static function endSingleJob() : void;

    /**
     * @throws Throwable
     *
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     *
     * This method is used when you want to run the event loop in a non-blocking way.
     * You should run this method in a separate thread and make it repeat every second.
     */
    public static function endMultiJobs() : void;
```
# How to use Async?
```php
    /**
     * This method is used to await a promise.
     */
    public static function await(callable|Promise|Async $callable) : mixed;

    /**
     * @throws Throwable
     *
     * This method is used to wait for all promises to be resolved.
     */
    public static function wait() : void;

    /**
     * This method is used to get the id of the promise.
     */
    public function getId() : int;
```
# How to use Promise?
```php
    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved.
     */
    public function then(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is rejected.
     */
    public function catch(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function resolve(int $id, mixed $result) : void;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function reject(int $id, mixed $result) : void;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all the promises fulfill, rejects when any of the promises rejects.
     */
    public static function all(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Settles when any of the promises settles.
     * In other words, fulfills when any of the promises fulfills, rejects when any of the promises rejects.
     */
    public static function race(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when any of the promises fulfills, rejects when all the promises reject.
     */
    public static function any(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all promises settle.
     */
    public static function allSettled(array $promises) : Promise;

    /**
     * @throws Throwable
     *
     * This method is used to get the id of the promise.
     */
    public function getId() : int; 
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

System::endSingleJob();
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

System::endSingleJob();
```
- Chaining Promises:
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

System::endSingleJob();
```
- Promise All:
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

System::endSingleJob();
```
- Time Out Function:
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
- Fetch & FecthFg
```php
$url = "https://www.google.com/";

System::fetch($url)->then(function($value) {
    var_dump($value);
})->catch(function($reason) {
    var_dump($reason);
});

System::endSingleJob();
```
```php
$url = "https://www.google.com/";

System::fetchFg($url)->then(function($value) {
    var_dump($value);
})->catch(function($reason) {
    var_dump($reason);
});

System::endSingleJob();
```
