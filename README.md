# Vapm
- One lib async/promise for PHP

# Async await
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
