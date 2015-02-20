<?php

namespace Corouser\Streamer;

class CoSocket
{
  use \Corouser\Scheduler\RetValTrait;
  use \Corouser\Streamer\WaitSocketsTrait;

  protected $socket;

  public function __construct($socket)
  {
    $this->socket = $socket;
  }

  public function accept()
  {
    yield self::waitForRead($this->socket); 
    yield self::retval(new CoSocket(stream_socket_accept($this->socket, 0)));
  }

  public function read($size)
  {
    yield self::waitForRead($this->socket);
    yield self::retval(fread($this->socket, $size));
  }

  public function write($string)
  {
    yield self::waitForWrite($this->socket);
    fwrite($this->socket, $string);
  }

  public function close()
  {
    @fclose($this->socket);
  }

}
