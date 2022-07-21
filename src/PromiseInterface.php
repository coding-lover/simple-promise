<?php
namespace Skelan\SimplePromise;


interface PromiseInterface
{
    public function then(callable $onFulfilled, callable $onRejected): PromiseInterface;

    public function otherwise(callable $onRejected): PromiseInterface;

    public function always(callable $onFulfilledOrRejected): PromiseInterface;

    public function cancel();
}
