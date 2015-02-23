<?php

namespace Corouser\Scheduler;

/**
 *  Defines an interface for each schedule client.
 *
 *  @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
interface ScheduleClientInterface
{
    /**
     * message from scheduler.
     *
     * @param string $msg  message/clients method name
     * @param array  $args message arguments
     * @return mixed
     */
    public function msgFromSchedule($msg, array $args);
}
