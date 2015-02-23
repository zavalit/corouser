<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\SystemCall;
use Corouser\Scheduler\TaskInterface;
use Corouser\Scheduler\ScheduleInterface;

/**
 * Trait to escape socket wait functions out of global namespace
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
trait WaitSocketsTrait
{
    /**
     * sys call to fill wait for read sockets before they go to scheduling
     *
     * @param resource $socket socket
     * @return SystemCall
     */
    private static function waitForRead($socket)
    {
        return new SystemCall(
            function (TaskInterface $task, ScheduleInterface $schedule) use ($socket) {
                $schedule->talkToClient('waitForRead', array($socket, $task));
            }
        );
    }

    /**
     * sys call to fill wait for write sockets before they go to scheduling
     *
     * @param resource $socket socket
     * @return SystemCall
     */
    private static function waitForWrite($socket)
    {
        return new \Corouser\Scheduler\SystemCall(
            function (TaskInterface $task, ScheduleInterface $schedule) use ($socket) {
                $schedule->talkToClient('waitForWrite', array($socket, $task));
            }
        );
    }
}
