<?php

namespace Corouser\Scheduler;

trait RetValTrait
{
  private static function retval($value)
  {
    return new \Corouser\Scheduler\CoroutineReturnValue($value);
  } 
}
