<?php

namespace Corouser\Scheduler;

/**
 * Trait to escape stackedCoroutine out of global namespace
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
trait StackedCoroutineTrait
{
    /**
     * Resolves yield calls tree
     * and gives a return value if outcome of yield is CoroutineReturnValue instance
     *
     * @param \Generator $coroutine nested coroutine tree
     * @return \Generator
     */
    private static function stackedCoroutine(\Generator $coroutine)
    {
        $stack = new \SplStack();
  
        while (true) {
            $value = $coroutine->current();
  
            // nested generator/coroutine
            if ($value instanceof \Generator) {
                $stack->push($coroutine);
                $coroutine = $value;
                continue;
            }
      
            // coroutine end or value is a value object instance
            if (!$coroutine->valid() || $value instanceof CoroutineReturnValue) {
                // if till this point, there are no coroutines in a stack thatn stop here
                if ($stack->isEmpty()) {
                    return;
                }
      
                $coroutine = $stack->pop();
                $value     = ($value instanceof CoroutineReturnValue) ? $value->getValue() : null;
                $coroutine->send($value);
      
                continue;
            }
      
            $coroutine->send(yield $coroutine->key() => $value);
        }
    }
}
