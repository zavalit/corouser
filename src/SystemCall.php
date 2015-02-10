<?php

require_once __DIR__ . "/Task.php";
require_once __DIR__ . "/Schedule.php";

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
