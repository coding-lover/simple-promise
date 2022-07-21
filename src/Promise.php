<?php
namespace Skelan\SimplePromise;


class Promise implements PromiseInterface
{
    /**
     * @var PromiseNode
     */
    private $firstNode;

    /**
     * @var PromiseNode
     */
    private $currentNode;

    /**
     * @var callable
     */
    private $canceller;

    public function __construct(callable $resolver, callable $canceller = null)
    {
        $this->initResolver($resolver);
        $this->canceller = $canceller;
    }

    private function initResolver($resolver)
    {
        call_user_func($resolver, function($value) {

            $this->resolveCall($this->firstNode, $this->makeResolve($value));

        }, function($reason) {

            $this->resolveCall($this->firstNode, $this->makeReject($reason));
        });
    }

    private function makeResolve($value)
    {
        return function(PromiseNode $node) use($value) {
            return $node->getOnFulfilled()($value);
        };
    }

    private function makeReject($reason)
    {
        return function(PromiseNode $node) use($reason) {
            return $node->getOnRejected()($reason);
        };
    }

    private function makeException(\Throwable $exception)
    {
        $this->triggerError($exception);

        return function (?PromiseNode $node) use($exception) {
            if(!$node instanceof PromiseNode || !is_callable($node->getOnRejected())) {
                return false;
            }

            // check onRejected parameter type
            $refFunc = new \ReflectionFunction($node->getOnRejected());
            $parameters = $refFunc->getParameters();

            if(empty($parameters[0])
                || !$parameters[0]->getClass()
                || (!$parameters[0]->getClass()->isInstance($exception))
            ) {
                return true;
            }

            $node->getOnRejected()($exception);
        };
    }

    private function triggerError($exception)
    {
        if((new \ReflectionClass($exception))->isSubclassOf(\Error::class)) {
            return trigger_error($exception->getMessage(), E_USER_ERROR);
        }

        return true;
    }

    private function resolveCall(?PromiseNode $node, callable $resolver)
    {
        $result = null;
        if(!$node instanceof PromiseNode) {
            return false;
        }

        try {
            $this->canceller = null;
            $result = $this->makeResolve($resolver($node));

        } catch (\Throwable $exception) {

            //call always
            $result = $this->makeException($exception);
        }

        if($node === null) {
            return $result;
        }

        $nextNode = $node->getNext();
        unset($node);

        return $this->resolveCall($nextNode, $result);
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then($onFulfilledOrRejected);
    }

    /**
     * @inheritDoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null): PromiseInterface
    {
        $promise = new PromiseNode($onFulfilled, $onRejected);

        //init first
        if($this->firstNode === null) {
            $this->firstNode = &$promise;
        }

        //link next
        if($this->currentNode !== null) {
            $this->currentNode->setNext($promise);
        }

        $this->currentNode = &$promise;

        return $this;
    }

    public function cancel()
    {
        if($this->canceller === null) {
            return false;
        }

        $node = $this->firstNode;
        $this->firstNode = $this->currentNode = null;
        $this->cleanPromiseNode($node);

        if(is_callable($this->canceller)) {
            return call_user_func($this->canceller, $this);
        }

        return true;
    }

    private function cleanPromiseNode(?PromiseNode $node)
    {
        if(!$node instanceof PromiseNode) {
            return true;
        }

        $nextNode = $node->getNext();
        unset($node);

        return $this->cleanPromiseNode($nextNode);
    }
}
