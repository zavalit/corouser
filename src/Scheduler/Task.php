<?php

namespace Scheduler;

class Task
{
  protected $task_id;

  protected $coroutine;

  protected $send_value;

  protected $is_before_first_yield = true;

  protected $exception = null;

  public function __construct($task_id, \Generator $coroutine)
  {
    $this->task_id = $task_id;
    
    $this->coroutine = stackedCoroutine($coroutine);
  }

  public function getTaskId()
  {
    return $this->task_id;
  }

  public function setSendValue($send_value)
  {
    $this->send_value = $send_value;
  }

  public function setException(\Exception $e)
  {
    $this->exception = $e;
  }

  public function run()
  {
    if($this->is_before_first_yield)
    {
      $this->is_before_first_yield = false;
   
      return $this->coroutine->current();
    }

    if($this->exception instanceof \Exception)
    {
      $retval = $this->coroutine->throw($this->exception);
      
      $this->exception = null;
      
      return $retval;
    }

    $retval = $this->coroutine->send($this->send_value);
    
    $this->send_value = null;

    return $retval;
  }

  public function isFinished()
  {
    return !$this->coroutine->valid();  
  }


}

function stackedCoroutine(\Generator $gen)
{
  $stack = new \SplStack();
  for(;;)
  {
    $value = $gen->current();
    
    // nested generator/coroutine
    if($value instanceof \Generator){
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

