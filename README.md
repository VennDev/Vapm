# Vapm
- One lib async/promise for PHP

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
# Promise
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
