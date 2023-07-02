# Vapm
- A library Async & Promise for PHP
- The method is based on Fibers, requires you to have php version from >= 8.1

# Async Await
```php
System::start();
function fetchData($url) : mixed {
    return new Async(function() use ($url) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = Async::await(fn() => curl_exec($curl));

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }

        curl_close($curl);

        return $response;
    });
}

function test() {
    $url = [
        "https://www.google.com",
        "https://www.youtube.com"
    ];
    
    foreach ($url as $value) {
        new Async(function() use ($value) {
            $res = Async::await(fetchData($value));
            var_dump($res);
        });
    }
}

test();
System::end();
```
# Promise
```php
System::start();

function testPromise($mode) : mixed {
    return new Promise(function() use ($mode) {
        if ($mode == 1) {
            Promise::resolve(1);
        } else {
            Promise::reject(1);
        }
    });
}

function test() {
    $promise = testPromise(2);
    $promise->then(function($value) {
        echo "resolve: $value\n";
    })->catch(function($value) {
        echo "reject: $value\n";
    });
}

test();
System::end();
```
# Async + Promise
```php
System::start();

function testPromise($mode) : mixed {
    return new Promise(function() use ($mode) {
        if ($mode == 1) {
            Promise::resolve(1);
        } else {
            Promise::reject(2);
        }
    });
}

function test() {
    new Async(function() {
        $await = Async::await(testPromise(2));
        var_dump($await);
    });
}

test();
System::end();
```
# Time Out function
```php
System::start();
function testAsync() {
    new Async(function() {
        Async::await(fn() => System::setTimeOut(function() {
            var_dump("Hello World 2");
        }, 1000));
    });
}

function test() {
    testAsync();
    var_dump("Hello World 1");
}

test();
System::end();
```
# How to use Async?
```php
interface InterfaceAsync
{

    /**
     * @param Promise|Async|callable $callable $callable $callable
     * @return mixed
     *
     * This function is used to create a new async await function
     * You should use this function in an async function
     */
    public static function await(Promise|Async|callable $callable) : mixed;

    /**
     * @return int
     *
     * This function is used to get the id of the async function
     */
    public function getId() : int;

}
```
# How to use Promise?
```php
interface InterfacePromise {

    /**
     * This is method getId for get id point of promise
     */
    public function getId() : int;

    /**
     * This is method then for add callback to promise
     */
    public function then(callable $callable) : Promise;

    /**
     * This is method catch for add callback to promise
     */
    public function catch(callable $callable) : Promise;

    /**
     * This is method finally for add callback to promise
     */
    public static function resolve(mixed $result) : void;

    /**
     * This is method finally for add callback to promise
     */
    public static function reject(mixed $result) : void;

}
```
# How to use method by System?
```php
interface InterfaceSystem {

    /**
     * This is a method used to make your program easier to understand
     * where the program begins and where it ends.
     */
    public static function start() : void;

    /**
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     */
    public static function end() : void;

    /**
     * This method is usually used at the end of the whole chunk of your program,
     * it is used to run the event loop.
     *
     * This method is used when you want to run the event loop in a non-blocking way.
     * You should run this method in a separate thread and make it repeat every second.
     */
    public static function endNonBlocking() : void;

    /**
     * This method is used to add a callable to the loop. The callable will
     * be executed after the timeOut.
     */
    public static function setTimeOut(callable $callable, float $timeOut) : void;

}
```
