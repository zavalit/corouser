<?php 

namespace Corouser\Streamer;

use Corouser\Scheduler\Task as TaskInterface;

class SocketTasks extends \ArrayObject
{
  public static function create($socket, TaskInterface $task)
  {
    return new static(array('socket'=>$socket, 
                            'tasks'=>[$task]));
  }

  public function addTask(TaskInterface $task)
  {
    $tasks = array_merge($this->offsetGet('tasks'), $task);
    $this->offsetSet('tasks', $tasks);
    return $this;
  }

  public function getSocket()
  {
    return $this->offsetGet('socket');
  }

  public function getTasks()
  {
    return $this->offsetGet('tasks');
  }

}
