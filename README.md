# Vapm
- A library Async & Promise for PHP
- The method is based on Fibers, requires you to have php version from >= 8.1

# Next update ?
- Simply add some other asynchronous features so that this library is as similar to Javascript as possible.

# How to use Async?
```php
    /**
     * This method is used to await a promise.
     */
    public static function await(mixed $callable) : mixed;

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
    public function then(callable $callable) : Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is rejected.
     */
    public function catch(callable $callable) : Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function resolve(mixed $result) : void;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function reject(mixed $result) : void;

    /**
     * @throws Throwable
     *
     * This method is used to add a callback|Promise|Async to the event loop.
     */
    public static function all(array $promises) : Async;

    /**
     * @throws Throwable
     *
     * This method is used to get the id of the promise.
     */
    public function getId() : int;
```
# How to use method by System?
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
    return new Promise(function() {
        Promise::resolve("Hello World");
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
function testPromise1() : Promise {
    return new Promise(function () {
        Promise::resolve("A");
    });
}

function testPromise2() : Promise {
    return new Promise(function () {
        Promise::resolve("B");
    });
}

function testPromise3() : Promise {
    return new Promise(function () {
        Promise::resolve("C");
    });
}

function testPromise4() : Promise {
    return new Promise(function () {
        Promise::resolve("D");
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
});

System::endSingleJob();
```
- Promise All:
```php
function promise1() : Promise {
    return new Promise(function() {
        Promise::resolve("promise1");
    });
}

function promise2() : Promise {
    return new Promise(function() {
        Promise::resolve("promise2");
    });
}

function promise3() : Promise {
    return new Promise(function() {
        Promise::resolve("promise3");
    });
}

function asyncTest() {
    new Async(function() {
        $promise = Async::await(Promise::all([
            promise1(),
            promise2(),
            promise3()
        ]));
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
