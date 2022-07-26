<?php
$dir = dirname(__DIR__);
require_once $dir . '/vendor/autoload.php';

$deferred = new \Skelan\SimplePromise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        return $x + 1;
    })
    ->then(function ($x) {
        throw new \Exception($x + 1);
    })
    ->otherwise(function (\Exception $x) {
        var_dump('otherwise: ' . ($x->getMessage() + 1)); //4
    })
    ->always(function () {
        var_dump('finally ');
    });

$deferred->resolve(1);
