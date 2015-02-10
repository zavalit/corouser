<?php

$port = 8081;

echo 'Start server on localhost port:'.$port.'...'.PHP_EOL;

$socket = @stream_socket_server('tcp://0.0.0.0:'.$port, $errNo, $errStr);
if(!$socket){
  throw new \Exception($errStr, $errNo);
}

while($conn=stream_socket_accept($socket)){

  fwrite($conn, 'The localtime year is '.date('Y'). PHP_EOL);
  usleep(100);
  fclose($conn);
  

}
