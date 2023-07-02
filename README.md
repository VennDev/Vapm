# Vapm
- A library Async for PHP
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
