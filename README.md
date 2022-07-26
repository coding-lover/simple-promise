Promise
=======

A lightweight implementation of [CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

This file mostly code come from [react/promise](https://github.com/reactphp/promise), thanks reactphp Team provide such a useful package.

Installation
--------
Make sure that you have composer installed [Composer](http://getcomposer.org/).

If you don't have Composer run the below command

```php
curl -sS https://getcomposer.org/installer | php
```
Run the installation
```php
composer require skelan/simple-promise
```

Concepts
--------

### Deferred

A **Deferred** represents a computation or unit of work that may not have
completed yet. Typically (but not always), that computation will be something
that executes asynchronously and completes at some point in the future.

### Promise

While a deferred represents the computation itself, a **Promise** represents
the result of that computation. Thus, each deferred has a promise that acts as
a placeholder for its actual result.

API
---

### Deferred

A deferred represents an operation whose resolution is pending. It has separate
promise and resolver parts.

```php
$deferred = new Skelan\SimplePromise\Deferred();

$promise = $deferred->promise();

$deferred->resolve(mixed $value = null);
$deferred->reject(mixed $reason = null);
```

### How promise forwarding works

A few simple examples to show how the mechanics of Promises/A forwarding works.
These examples are contrived, of course, and in real usage, promise chains will
typically be spread across several function calls, or even several levels of
your application architecture.

#### Resolution forwarding

Resolved promises forward resolution values to the next promise.
The first promise, `$deferred->promise()`, will resolve with the value passed
to `$deferred->resolve()` below.

Each call to `then()` returns a new promise that will resolve with the return
value of the previous handler. This creates a promise "pipeline".

```php
$deferred = new Skelan\SimplePromise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        // $x will be the value passed to $deferred->resolve() below
        // and returns a *new promise* for $x + 1
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 2
        // This handler receives the return value of the
        // previous handler.
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 3
        // This handler receives the return value of the
        // previous handler.
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 4
        // This handler receives the return value of the
        // previous handler.
        echo 'Resolve ' . $x;
    });

$deferred->resolve(1); // Prints "Resolve 4"
```

#### Rejection forwarding

Rejected promises behave similarly, and also work similarly to try/catch:
When you catch an exception, you must rethrow for it to propagate.

Similarly, when you handle a rejected promise, to propagate the rejection,
"rethrow" it by either returning a rejected promise, or actually throwing
(since promise translates thrown exceptions into rejections)

```php
$deferred = new Skelan\SimplePromise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        throw new \Exception($x + 1);
    })
    ->otherwise(function (\Exception $x) {
        // Propagate the rejection
        throw $x;
    })
    ->otherwise(function ($x) {
        echo 'Reject ' . $x->getMessage(); // 3
    });

$deferred->resolve(1);  // Prints "Reject 3"
```

### Best practices
#### 1) Try/catch demo
Just like try/catch, you can choose to propagate or not. Mixing resolutions and
rejections will still forward handler results in a predictable way.
```php 
try {
  return doSomething();
} catch(\Exception $e) {
    return handleError($e);
} finally {
    cleanup();
}
```

```php
$deferred = new Skelan\SimplePromise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        return $x + 1;
    })
    ->then(function ($x) {
        throw new \Exception($x + 1);
    })
    ->otherwise(function (\Exception $x) {
        return $x->getMessage() + 1;
    })
    ->then(function ($x) {
        echo 'Mixed ' . $x; // 4
    });

$deferred->resolve(1);  // Prints "Mixed 4"
```

#### 2) Asynchronous call
[swoole is awesome extension of php](https://github.com/swoole/swoole-src) 
```php 
function remoteRequest() {
    $deferred = new \Skelan\SimplePromise\Deferred(function (\Skelan\SimplePromise\PromiseInterface $promise) {
        var_dump('call cancel.');
    });

    \Swoole\Timer::after(1000, function() use($deferred) {
        $deferred->resolve('finish: ' . time());
    });

    return $deferred->promise();
}

remoteRequest()->then(function ($value) {
    var_dump('resolve: ' . time());
    var_dump($value);
    throw new \Exception('xxx');
}, function($reason) {
    var_dump($reason);
})->otherwise(function(\Throwable $exception) {
    var_dump('exception: ' . $exception->getMessage());
})->always(function($value) {
    var_dump('otherwise: ' . $value);
});
```

License
-------

Released under the [MIT](LICENSE) license.
