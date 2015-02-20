<?php

namespace Corouser\Scheduler;

trait NewTaskTrait
{
  private static function newTask(\Generator $coroutine)
  {
    return new SystemCall(function(Task $task, Schedule $schedule) use ($coroutine){
      $task->setSendValue($schedule->addTask($coroutine));
      $schedule->scheduleTask($task);
    });
  }

}
