# Vapm
- A library async/promise for PHP
- The method is based on Fibers, requires you to have php version from >= 8.1

# Async Await
```php
function test() : Async { 
    return Async::create(function() {

        try {
            $url = [
                "https://www.google.com",
                "https://www.youtube.com"
            ];
            
            foreach ($url as $value) {
                var_dump(Async::await(fn() => fetchData($value)));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    });
}
```
# MultiAsync Await
```php
function fetchData($url) : mixed {
    $async = Async::create(function() use ($url) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($curl);

        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }

        curl_close($curl);

        return $response;
    });
    return Async::await(fn() => $async);
}

function test() : mixed { 
    $async = Async::create(function() {
        $response = fetchData("https://www.google.com");
        return $response;
    });
    return Async::await(fn() => $async);
}

var_dump(test());
```
# Promise All
```php
function test() : mixed { 
    
    $pro1 = new Promise(function() {
        return Promise::resolve(fetchData("https://www.youtube.com"));
    });

    $pro2 = new Promise(function() {
        return Promise::resolve(fetchData("https://www.google.com"));
    });

    return Promise::all([$pro1, $pro2]);
}

test()->then(function($result) {
    var_dump($result);
})->catch(function($error) {
    var_dump($error);
});
```
# Chaining Promises
```php
function function1() : mixed {
    return new Promise(function() {
        sleep(2);
        return Promise::resolve(6);
    });
}

function function2() : mixed {
    return new Promise(function() {
        sleep(2);
        return Promise::resolve(6);
    });
}

function test() : mixed { 
    return function1()->then(function($result) {
        var_dump($result);
        return function2()->then(function($result) {
            var_dump($result);
        });
    });
}

test();
```
# Promise + Async
```php
function fetchData($url) : mixed {
    return Async::create(function() use ($url) {
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

function test() : mixed { 
    return new Promise(function() {
        return Promise::resolve(fetchData("https://www.google.com")->getResult());
    });
}

test()->then(function($result) {
    var_dump($result);
})->catch(function($error) {
    var_dump($error);
});
```
