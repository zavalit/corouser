<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\TaskInterface;

/**
 * A list of tasks grouped by socker ids
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class SocketsWaitList extends \ArrayObject
{
    /**
     * add socket and a task, that belongs to that socket
     *
     * @param resource      $socket socket
     * @param TaskInterface $task   task
     * @return void
     */
    public function addSocketTask($socket, TaskInterface $task)
    {
        $socket_id = (int) $socket;
  
        if ($this->offsetExists($socket_id)) {
            $this->offsetGet($socket_id)->addTask($task);
        } else {
            $this->offsetSet($socket_id, SocketTasks::create($socket, $task));
        }
    }

    /**
     * get sockets that are stored in the list
     * @return array
     */
    public function getSockets()
    {
        $sockets  = [];
        $iterator = $this->getIterator();
        while ($iterator->valid()) {
            $sockets[] = $iterator->current()->getSocket();
            $iterator->next();
        }

        return $sockets;
    }

    /**
     * get tasks for given sockets and remove it out of the list
     *
     * @param array $sockets array of sockets
     * @return \Generator
     */
    public function purgeSocketsAndGetTasks(array $sockets)
    {
        foreach ($sockets as $socket) {
            $socket_id    = (int) $socket;
            $socket_tasks = $this->offsetGet($socket_id);
            $this->offsetUnset($socket_id);
            foreach ($socket_tasks->getTasks() as $task) {
                yield $task;
            }
        }
    }
}
