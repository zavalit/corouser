<?php

namespace Corouser\Scheduler;

use Corouser\Scheduler\SystemCall;
use Corouser\Scheduler\TaskInterface;
use Corouser\Scheduler\ScheduleInterface;

/**
 * Provides a SystemCall instance that wrapps a callback for
 * a given coroutine with arguments Task and Scheduler
 *
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
trait NewTaskTrait
{
    /**
     * add a callback function that adds a new task to scheduler
     *
     * @param \Generator $coroutine Coroutine
     * @return SystemCall
     */
    private static function newTask(\Generator $coroutine)
    {
        return new SystemCall(
            function (TaskInterface $task, ScheduleInterface $schedule) use ($coroutine) {
                $task->setSendValue($schedule->addTask($coroutine));
                $schedule->scheduleTask($task);
            }
        );
    }
}
