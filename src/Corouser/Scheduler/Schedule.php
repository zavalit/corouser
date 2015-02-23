<?php

namespace Corouser\Scheduler;

use Corouser\Scheduler\ScheduleClientInterface;
use Corouser\Scheduler\TaskInterface;
use Corouser\Scheduler\ScheduleInterface;

/**
 * Schedules tasks in a queue and run/requeue each of them
 *
 * @see    ScheduleInterface
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class Schedule implements ScheduleInterface
{
    /**
     * task_id_iterator.
     *
     * @var int
     */
    protected $task_id_iterator = 0;

    /**
     * taskMap.
     *
     * @var array
     */
    protected $taskMap = array();

    /**
     * taskQueue.
     *
     * @var mixed
     */
    protected $taskQueue;

    /**
     * client.
     *
     * @var mixed
     */
    protected $client;

    /**
     * __construct.
     *
     * @param \SplQueue $task_queue queue of tasks
     */
    public function __construct(\SplQueue $task_queue)
    {
        $this->taskQueue = $task_queue;
    }

    /**
     * regsiter client that uses schedule.
     *
     * @param ScheduleClientInterface $client schedule client
     * @return void
     */
    public function registerClient(ScheduleClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * message on registerd client.
     *
     * @param mixed $msg  message
     * @param array $args message arguments
     * @return void
     */
    public function talkToClient($msg, array $args)
    {
        $this->client->msgFromSchedule($msg, $args);
    }

    /**
     * addTask.
     *
     * @param \Generator $coroutine coroutine
     * @return void
     */
    public function addTask(\Generator $coroutine)
    {
        $task_id = ++$this->task_id_iterator;

        $task = new Task($task_id, $coroutine);

        $this->taskMap[$task_id] = $task;

        $this->scheduleTask($task);

        return $task_id;
    }

    /**
     * get task queue.
     *
     * @return \SplQueue
     */
    public function getTaskQueue()
    {
        return $this->taskQueue;
    }

    /**
     * schedule a task.
     *
     * @param TaskInterface $task Task
     * @return void
     */
    public function scheduleTask(TaskInterface $task)
    {
        $this->taskQueue->enqueue($task);
    }

    /**
     * run scheduled tasks and optionally call systemcalls.
     * @return void
     */
    public function run()
    {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();

            $retval = $task->run();

            if ($retval instanceof SystemCall) {
                $retval($task, $this);
                continue;
            }

            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->scheduleTask($task);
            }
        }
    }

    /**
     * remove task out of thw queue.
     *
     * @param int $tid task id
     *
     * @return bool
     */
    public function killTask($tid)
    {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }

        unset($this->taskMap[$tid]);

        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }

        return true;
    }
}
