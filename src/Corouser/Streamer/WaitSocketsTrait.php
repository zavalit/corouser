<?php

namespace Corouser\Streamer;

trait WaitSocketsTrait
{
   private static function waitForRead($socket)
   {
     return new \Corouser\Scheduler\SystemCall(function(\Corouser\Scheduler\Task $task, \Corouser\Scheduler\Schedule $schedule) use ($socket){
       $schedule->talkToClient('waitForRead', array($socket, $task));
     });
   }
   
   private static function waitForWrite($socket)
   {
     return new \Corouser\Scheduler\SystemCall(function(\Corouser\Scheduler\Task $task, \Corouser\Scheduler\Schedule $schedule) use ($socket){
       $schedule->talkToClient('waitForWrite', array($socket, $task));
     });
   }
}
