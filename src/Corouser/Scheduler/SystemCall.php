<?php

namespace Corouser\Scheduler;

use Corouser\Scheduler\TaskInterface;
use Corouser\Scheduler\ScheduleInterface;

/**
 * SystemCall defines a signature for callback function
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class SystemCall
{
    /**
     * callback function
     *
     * @var callable
     */
    protected $callback;

    /**
     * init SystemCall
     *
     * @param callable $callback function to execute
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * execute callback function
     *
     * @param TaskInterface     $task     task
     * @param ScheduleInterface $schedule scheduler
     * @return mixed
     */
    public function __invoke(TaskInterface $task, ScheduleInterface $schedule)
    {
        $callback = $this->callback;

        return $callback($task, $schedule);
    }
}
