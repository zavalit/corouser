<?php

require_once "CoroutineReturnValue.php";

function stackedCoroutine(Generator $gen)
{
  $stack = new SplStack();
  for(;;)
  {
    $value = $gen->current();
    
    // nested generator/coroutine
    if($value instanceof Generator){
      $stack->push($gen);
      $gen = $value;
      continue;
    }
    
    // coroutine end or value is a value object instance 
    if(!$gen->valid() || $value instanceof CoroutineReturnValue)
    {
      // if till this point, there are no coroutines in a stack thatn stop here
      if($stack->isEmpty()){
        return;
      }

      $gen = $stack->pop();
      $value = ($value instanceof CoroutineReturnValue)?$value->getValue():NULL;
      $gen->send($value);
      
      continue;

    }

    $gen->send(yield $gen->key() => $value);

  }

}

