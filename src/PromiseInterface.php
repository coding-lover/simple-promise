<?php
namespace CodingLover\SimplePromise;


interface PromiseInterface
{
    public function then(callable $onFulfilled, callable $onRejected): PromiseInterface;

    public function otherwise(callable $onRejected);

    public function always(callable $onFulfilledOrRejected);

    public function cancel();
}
