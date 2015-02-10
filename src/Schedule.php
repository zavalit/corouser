<?php

require_once __DIR__ . "/Task.php";
require_once __DIR__ . "/SystemCall.php";

class Schedule
{
  protected $task_id_iterator = 0;
  
  protected $taskMap = array();

  protected $taskQueue;

  protected $wait_for_read = array();

  protected $wait_for_write = array();

  public function __construct()
  {
    $this->taskQueue = new \SplQueue();
  }

  public function addTask(\Generator $coroutine)
  {
    $task_id = ++$this->task_id_iterator;
    
    $task = new Task($task_id, $coroutine);
    
    $this->taskMap[$task_id] = $task;
    
    $this->scheduleTask($task); 
    
    return $task_id;
  }

  public function scheduleTask($task)
  {
    $this->taskQueue->enqueue($task);
  }

  public function run()
  {

    $this->addTask($this->ioPollTask());

    while(!$this->taskQueue->isEmpty())
    {
      $task = $this->taskQueue->dequeue();
     
      $retval = $task->run();

      if($retval instanceof SystemCall)
      {
        $retval($task, $this);
        continue;
      }
      
      if($task->isFinished())
      {
        unset($this->taskMap[$task->getTaskId()]);
      }
      else{
        $this->scheduleTask($task);
      }
    } 
  }

  public function killTask($tid) {
    
    if (!isset($this->taskMap[$tid])) {
        return false;
    }

    unset($this->taskMap[$tid]);

    // This is a bit ugly and could be optimized so it does not have to walk the queue,
    // but assuming that killing tasks is rather rare I won't bother with it now
    foreach ($this->taskQueue as $i => $task) {
        if ($task->getTaskId() === $tid) {
            unset($this->taskQueue[$i]);
            break;
        }
    }

    return true;
  }

  public function waitForRead($socket, Task $task)
  {
    $socket_id = (int) $socket;
    if(isset($this->wait_for_read[$socket_id])){
      $this->wait_for_read[$socket_id][1][] = $task;
    }
    else{
      $this->wait_for_read[$socket_id] = [$socket, [$task]];
    }
  }

  public function waitForWrite($socket, Task $task)
  { 
    $socket_id = (int) $socket;
    if(isset($this->wait_for_write[$socket_id])){
      $this->wait_for_write[$socket_id][1][] = $task;
    }
    else{
      $this->wait_for_write[$socket_id] = [$socket, [$task]];
    }
  }

  protected function ioPoll($timeout)
  {
    
    $rSocks = [];
    foreach($this->wait_for_read as list($socket)){
      $rSocks[] = $socket;
    }

    $wSocks = [];
    foreach($this->wait_for_write as list($socket)){
      $wSocks[] = $socket;
    }

    $eSocks = [];

    if(!@stream_select($rSocks, $wSocks, $eSocks, $timeout)){
      return;  
    }

    foreach($rSocks as $socket){
      list(, $tasks) = $this->wait_for_read[(int)$socket];
      unset($this->wait_for_read[(int)$socket]);

      foreach($tasks as $task)
      {
        $this->scheduleTask($task);
      }

    }
  
    foreach($wSocks as $socket){
      list(, $tasks) = $this->wait_for_write[(int)$socket];
      unset($this->wait_for_write[(int)$socket]);

      foreach($tasks as $task)
      {
        $this->scheduleTask($task);
      }
    }
  
  }

  protected function ioPollTask()
  {
    while(true){
      if($this->taskQueue->isEmpty()){
        $this->ioPoll(null);
      }else{
        $this->ioPoll(0);
      }
      yield;
      
    }
  }

}

