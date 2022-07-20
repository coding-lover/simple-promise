<?php

namespace CodingLover\SimplePromise;

class Deferred
{
    /**
     * @var callable
     */
    private $resolveCallback = null;

    /**
     * @var callable
     */
    private $rejectCallback = null;

    /**
     * @var Promise
     */
    private $promise;

    private $canceller;

    public function __construct(callable $canceller = null)
    {
        $this->canceller = $canceller;
    }

    public function promise(): PromiseInterface
    {
        if($this->promise === null) {
            $this->promise = new Promise(function($resolver, $rejected) {
                $this->resolveCallback = $resolver;
                $this->rejectCallback = $rejected;
            }, $this->canceller);
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        call_user_func($this->resolveCallback, $value);
    }

    public function reject($value = null)
    {
        $this->promise();

        call_user_func($this->rejectCallback, $value);
    }
}
