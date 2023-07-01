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
function fetchData($url) : mixed {
    return (new Promise(function() use ($url) {
        $curl = curl_init();
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($curl);

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return Promise::reject("Error: " . $error);
        }

        curl_close($curl);

        return Promise::resolve($response);
    }))->getResult();
}

function test() : mixed {
    
    $pr1 = new Promise(function() {
        return Promise::resolve("Hello");
    });

    $pr2 = new Promise(function() {
        return Promise::resolve("World");
    });

    return Promise::all([
        $pr1,
        $pr2
    ]);
}

test()->then(function($res) {
    var_dump($res);
});

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
