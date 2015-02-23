<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\TaskInterface;

/**
 * Collection of tasks for a certain socket
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class SocketTasks extends \ArrayObject
{
    /**
     * create an instance with a socket and corelated task
     *
     * @param resource      $socket socket
     * @param TaskInterface $task   task
     * @return SocketTasks
     */
    public static function create($socket, TaskInterface $task)
    {
        return new static(array('socket' => $socket,
                              'tasks' => [$task], ));
    }

    /**
     * add task to this collection instance
     *
     * @param TaskInterface $task task
     * @return SocketTasks
     */
    public function addTask(TaskInterface $task)
    {
        $tasks = array_merge($this->offsetGet('tasks'), $task);
        $this->offsetSet('tasks', $tasks);

        return $this;
    }

    /**
     * get socket
     * @return resource
     */
    public function getSocket()
    {
        return $this->offsetGet('socket');
    }

    /**
     * get tasks
     * @return array
     */
    public function getTasks()
    {
        return $this->offsetGet('tasks');
    }
}
