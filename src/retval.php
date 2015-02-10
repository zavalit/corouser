<?php

require_once "CoroutineReturnValue.php";

function retval($value)
{
  return new CoroutineReturnValue($value);
}

