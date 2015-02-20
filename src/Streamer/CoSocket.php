<?php

namespace Streamer;

class CoSocket
{

  protected $socket;

  public function __construct($socket)
  {
    $this->socket = $socket;
  }

  public function accept()
  {
    yield waitForRead($this->socket); 
    $value = retval(new CoSocket(stream_socket_accept($this->socket, 0)));
    yield $value; 
  }

  public function read($size)
  {
    yield waitForRead($this->socket);
    yield retval(fread($this->socket, $size));
  }

  public function write($string)
  {
    yield waitForWrite($this->socket);
    fwrite($this->socket, $string);
  }

  public function close()
  {
    @fclose($this->socket);
  }

}

function retval($value)
{
  return new \Scheduler\CoroutineReturnValue($value);
}

function waitForRead($socket)
{
  return new \Scheduler\SystemCall(function(\Scheduler\Task $task, \Scheduler\Schedule $schedule) use ($socket){
    $schedule->talkToClient('waitForRead', array($socket, $task));
  });
}

function waitForWrite($socket)
{
  return new \Scheduler\SystemCall(function(\Scheduler\Task $task, \Scheduler\Schedule $schedule) use ($socket){
    $schedule->talkToClient('waitForWrite', array($socket, $task));
  });
}


