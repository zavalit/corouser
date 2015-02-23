<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\TaskInterface;
use Corouser\Scheduler\ScheduleInterface;
use Corouser\Scheduler\ScheduleClientInterface;

/**
 * Schedule Client for Streaming
 *
 * @see ScheduleClientInterface
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class StreamSchedule implements ScheduleClientInterface
{
    /**
     * Schedule
     *
     * @var ScheduleInterface
     */
    protected $schedule;

    /**
     * sockets and relevant tasks waiting to be read
     *
     * @var array
     */
    protected $wait_for_read = array();

    /**
     * sockets and relevant tasks waiting to be written
     *
     * @var array
     */
    protected $wait_for_write = array();

    /**
     * init StreamSchedule
     *
     * @param ScheduleInterface $schedule scheduler that executes tasks
     * @return void
     */
    public function __construct(ScheduleInterface $schedule)
    {
        $this->schedule = $schedule;
        $schedule->registerClient($this);
        $this->wait_for_read  = new SocketsWaitList();
        $this->wait_for_write = new SocketsWaitList();
    }

    /**
     * message notifed by scheduler
     *
     * @param mixed $msg  message
     * @param array $args arguments
     * @return mixed
     */
    public function msgFromSchedule($msg, array $args)
    {
        return call_user_func_array(array($this, $msg), $args);
    }

    /**
     * run scheduling
     * @return void
     */
    public function run()
    {
        $this->schedule->addTask($this->ioPollTask());
        $this->schedule->run();
    }

    /**
     * fill waiting read sockets with task
     *
     * @param resource      $socket socket
     * @param TaskInterface $task   task
     * @return void
     */
    public function waitForRead($socket, TaskInterface $task)
    {
        $this->wait_for_read->addSocketTask($socket, $task);
    }

    /**
     * fill waiting write sockets with task
     *
     * @param resource      $socket socket
     * @param TaskInterface $task   task
     * @return void
     */
    public function waitForWrite($socket, TaskInterface $task)
    {
        $this->wait_for_write->addSocketTask($socket, $task);
    }

    /**
     * resolve waiting sockets and scheduling them
     *
     * @param int $timeout timeout
     * @return void
     */
    protected function ioPoll($timeout)
    {
        $rSocks = $this->wait_for_read->getSockets();

        $wSocks = $this->wait_for_write->getSockets();

        $eSocks = [];

        if (!@stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            return;
        }

        $this->scheduleReadTasks($rSocks);

        $this->scheduleWriteTasks($wSocks);
    }

    /**
     * schedule read tasks
     *
     * @param array $sockets sockets
     * @return void
     */
    protected function scheduleReadTasks(array $sockets)
    {
        foreach ($this->wait_for_read->purgeSocketsAndGetTasks($sockets) as $task) {
            $this->schedule->scheduleTask($task);
        }
    }

    /**
     * schedule write tasks
     *
     * @param array $sockets sockets
     * @return void
     */
    protected function scheduleWriteTasks(array $sockets)
    {
        foreach ($this->wait_for_write->purgeSocketsAndGetTasks($sockets) as $task) {
            $this->schedule->scheduleTask($task);
        }
    }

    /**
     * entry task for socket select
     * @return \Generator
     */
    protected function ioPollTask()
    {
        while (true) {
            if ($this->schedule->getTaskQueue()->isEmpty()) {
                $this->ioPoll(null);
            } else {
                $this->ioPoll(0);
            }
            yield;
        }
    }
}
