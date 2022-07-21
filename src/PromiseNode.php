<?php
namespace Skelan\SimplePromise;

class PromiseNode
{
    /**
     * @var callable
     */
    protected $onFulfilled;

    /**
     * @var callable
     */
    protected $onRejected;

    /**
     * @var self
     */
    protected $next;

    public function __construct(callable $onFulfilled = null, callable $onRejected = null, self $next = null)
    {
        $this->onFulfilled = $onFulfilled;
        $this->onRejected = $onRejected;
        $this->next = $next;
    }

    /**
     * @return callable
     */
    public function getOnFulfilled(): ?callable
    {
        return $this->onFulfilled;
    }

    /**
     * @param callable $onFulfilled
     */
    public function setOnFulfilled(callable $onFulfilled): void
    {
        $this->onFulfilled = $onFulfilled;
    }

    /**
     * @return callable
     */
    public function getOnRejected(): ?callable
    {
        return $this->onRejected;
    }

    /**
     * @param callable $onRejected
     */
    public function setOnRejected(callable $onRejected): void
    {
        $this->onRejected = $onRejected;
    }

    /**
     * @return PromiseNode
     */
    public function getNext(): ?PromiseNode
    {
        return $this->next;
    }

    /**
     * @param PromiseNode $next
     */
    public function setNext(PromiseNode $next): void
    {
        $this->next = $next;
    }

}
