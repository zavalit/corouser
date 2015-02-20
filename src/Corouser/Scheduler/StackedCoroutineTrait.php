<?php

namespace Corouser\Scheduler;

trait StackedCoroutineTrait
{
  private static function stackedCoroutine(\Generator $coroutine)
  {
    $stack = new \SplStack();
    
    while(true)
    {
      $value = $coroutine->current();
      
      // nested generator/coroutine
      if($value instanceof \Generator){
        $stack->push($coroutine);
        $coroutine = $value;
        continue;
      }
      
      // coroutine end or value is a value object instance 
      if(!$coroutine->valid() || $value instanceof CoroutineReturnValue)
      {
        // if till this point, there are no coroutines in a stack thatn stop here
        if($stack->isEmpty()){
          return;
        }
  
        $coroutine = $stack->pop();
        $value = ($value instanceof CoroutineReturnValue)?$value->getValue():NULL;
        $coroutine->send($value);
        
        continue;
  
      }
  
      $coroutine->send(yield $coroutine->key() => $value);
    }

  }
}
