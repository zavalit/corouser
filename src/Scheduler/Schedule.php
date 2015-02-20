<?php

namespace Scheduler;


class Schedule
{
  protected $task_id_iterator = 0;
  
  protected $taskMap = array();

  protected $taskQueue;

  protected $client;

  public function __construct(\SplQueue $task_queue)
  {
    $this->taskQueue = $task_queue;
  }

  public function registerClient($client)
  {
    $this->client = $client;
  }

  public function talkToClient($msg, $args)
  {
    $this->client->msgFromSchedule($msg, $args);
  }



  public function addTask(\Generator $coroutine)
  {
    $task_id = ++$this->task_id_iterator;
    
    $task = new Task($task_id, $coroutine);
    
    $this->taskMap[$task_id] = $task;
    
    $this->scheduleTask($task); 
    
    return $task_id;
  }

  public function getTaskQueue()
  {
    return $this->taskQueue;
  }

  public function scheduleTask($task)
  {
    $this->taskQueue->enqueue($task);
  }

  public function run()
  {


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

}

