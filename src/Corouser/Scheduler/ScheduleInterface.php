<?php

namespace Corouser\Scheduler;

/**
 * Defines Schedule Api
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
interface ScheduleInterface
{
    
    /**
     * runs schedule.
     *
     * @return mixed
     */
    public function run();

    /**
     * register schedule's client
     *
     * @param ScheduleClientInterface $client Client
     * @return void
     */
    public function registerClient(ScheduleClientInterface $client);

    /**
     * get tasks queue
     *
     * @return \SplQueue
     */
    public function getTaskQueue();
}
