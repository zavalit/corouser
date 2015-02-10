<?php

require_once 'Schedule.php';
require_once 'Task.php';
require_once 'SystemCall.php';

function task($msg, $max)
{
  $i=0;
  while($i++<$max){
    echo "$i from $max for $msg".PHP_EOL;
    yield;
  }

}

function bigtask()
{
  task('bar', 10);
  
  foreach(task('foo', 5) as $row){
    yield $row;
  }
}

function run(Generator $gen)
{
  while(true){
    $gen->send(yield $gen->key() => $gen->current());
  }  
}




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

#$schedule = new Schedule();
#$schedule->addTask(bigtask());
#$schedule->run();
