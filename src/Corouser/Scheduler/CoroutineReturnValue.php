<?php

namespace Corouser\Scheduler;

class CoroutineReturnValue
{
  protected $value;

  public function __construct($value)
  {
    $this->value = $value;
  }

  public function getValue()
  {
    return $this->value;
  }

}