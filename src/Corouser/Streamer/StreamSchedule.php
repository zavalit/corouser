<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\Task;
use Corouser\Scheduler\Schedule;

class StreamSchedule
{

  protected $schedule;

  protected $wait_for_read = array();

  protected $wait_for_write = array();

  public function __construct(Schedule $schedule)
  {
    $this->schedule = $schedule;
    $schedule->registerClient($this);
  }

  public function msgFromSchedule($msg, $args)
  {
    return call_user_func_array(array($this, $msg), $args);
  }

  public function run()
  {
    $this->schedule->addTask($this->ioPollTask());
    $this->schedule->run();
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
        $this->schedule->scheduleTask($task);
      }

    }
  
    foreach($wSocks as $socket){
      list(, $tasks) = $this->wait_for_write[(int)$socket];
      unset($this->wait_for_write[(int)$socket]);

      foreach($tasks as $task)
      {
        $this->schedule->scheduleTask($task);
      }
    }
  
  }

  protected function ioPollTask()
  {
    while(true){
      if($this->schedule->getTaskQueue()->isEmpty()){
        $this->ioPoll(null);
      }else{
        $this->ioPoll(0);
      }
      yield;
      
    }
  }

  
}
