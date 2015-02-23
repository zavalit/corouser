<?php

namespace Corouser\Scheduler;

/**
 * Interface for Schedule Tasks.
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
interface TaskInterface
{
    /**
     * execute task.
     *
     * @return mixed
     */
    public function run();

    /**
     * get task id.
     *
     * @return int
     */
    public function getTaskId();

    /**
     * checkes whether the task is finished.
     *
     * @return bool
     */
    public function isFinished();
}
