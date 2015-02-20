<?php

namespace Corouser\Scheduler;

class SystemCall
{
  protected $callback;

  public function __construct(callable $callback)
  {
    $this->callback = $callback;
  }

  public function __invoke(Task $task, Schedule $schedule)
  {
    $callback = $this->callback;
    return $callback($task, $schedule);
  }

}
