<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\Task as TaskInterface;
use Corouser\Streamer\SocketTasks;


class SocketsWaitList extends \ArrayObject
{
  public function addSocketTask($socket, TaskInterface $task)
  { 
    $socket_id = (int) $socket;

    if($this->offsetExists($socket_id)){
    
      $this->offsetGet($socket_id)->addTask($task); 
    
    }
    else{

      $this->offsetSet($socket_id, SocketTasks::create($socket, $task));
    
    }
  }

  public function getSockets()
  {
    $sockets = [];
    $iterator = $this->getIterator();
    while($iterator->valid())
    {
      $sockets[] = $iterator->current()->getSocket();
      $iterator->next();
    }

    return $sockets;
    
  }

  public function purgeSocketsAndGetTasks(array $sockets)
  {
    foreach($sockets as $socket)
    {
      $socket_id = (int)$socket;
      $socket_tasks = $this->offsetGet($socket_id);
      $this->offsetUnset($socket_id);
      foreach($socket_tasks->getTasks() as $task)
      {
        yield $task;
      }
    } 
  }

  
}
