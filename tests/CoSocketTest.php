<?php

require_once '../vendor/autoload.php';

class CoSocketTest 
{
  function testAccept()
  {
    $sut = new \Streamer\CoSocket("1");
    $coroutine = $sut->accept();
    var_dump($coroutine->current());
    $coroutine->next();
    var_dump($coroutine->current());
    die(var_dump($coroutine));
  }

}

(new CoSocketTest())->testAccept();
