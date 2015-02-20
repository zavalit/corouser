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
    $this->wait_for_read = new SocketsWaitList();
    $this->wait_for_write = new SocketsWaitList();
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
    $this->wait_for_read->addSocketTask($socket, $task);
  }

  public function waitForWrite($socket, Task $task)
  { 
    $this->wait_for_write->addSocketTask($socket, $task);
  }

  protected function ioPoll($timeout)
  {
    $rSocks = $this->wait_for_read->getSockets();

    $wSocks = $this->wait_for_write->getSockets();

    $eSocks = [];

    if(!@stream_select($rSocks, $wSocks, $eSocks, $timeout)){
      return;  
    }

    $this->scheduleReadTasks($rSocks);

    $this->scheduleWriteTasks($wSocks);
  
  }

  protected function scheduleReadTasks(array $sockets)
  {
    foreach($this->wait_for_read->purgeSocketsAndGetTasks($sockets) as $task)
    {
        $this->schedule->scheduleTask($task);
    }
  }

  protected function scheduleWriteTasks(array $sockets)
  {
    foreach($this->wait_for_write->purgeSocketsAndGetTasks($sockets) as $task)
    {
        $this->schedule->scheduleTask($task);
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
