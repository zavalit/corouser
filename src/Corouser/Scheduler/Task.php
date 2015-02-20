<?php

namespace Corouser\Scheduler;

class Task
{

  use \Corouser\Scheduler\StackedCoroutineTrait;

  protected $task_id;

  protected $coroutine;

  protected $send_value;

  protected $is_before_first_yield = true;

  protected $exception = null;

  public function __construct($task_id, \Generator $coroutine)
  {
    $this->task_id = $task_id;
    
    $this->coroutine = self::stackedCoroutine($coroutine);
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

