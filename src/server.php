<?php

require_once 'Schedule.php';
require_once 'Task.php';
require_once 'SystemCall.php';
require_once 'CoroutineReturnValue.php';
require_once 'CoSocket.php';
require_once 'retval.php';
require_once 'stackedCoroutine.php';

function newTask(Generator $coroutine)
{
  return new SystemCall(function(Task $task, Schedule $schedule) use ($coroutine){
    $task->setSendValue($schedule->addTask($coroutine));
    $schedule->scheduleTask($task);
  });
}


function waitForRead($socket)
{
  return new SystemCall(function(Task $task, Schedule $schedule) use ($socket){
    $schedule->waitForRead($socket, $task);
  });
}

function waitForWrite($socket)
{
  return new SystemCall(function(Task $task, Schedule $schedule) use ($socket){
    $schedule->waitForWrite($socket, $task);
  });
}


function server($port)
{
  echo "Starting localhost server on port:$port";

  $socket = @stream_socket_server("tcp://0.0.0.0:$port", $errNo, $errStr);
  if(!$socket){
    throw new \Exception($errStr, $errNo);
  }

  stream_set_blocking($socket, 0);

  $coSocket = new CoSocket($socket);

  while(true){
    yield newTask(
      handleClient(yield $coSocket->accept())
    );   
  }
}

function handleClient(CoSocket $socket)
{
  $data = (yield $socket->read(8192)); 

  $msg = "Recieved following request:".PHP_EOL.PHP_EOL.$data;
  $msgLength = strlen($msg);
  
  $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;
  
  yield $socket->write($response);
  yield $socket->close();

}


$schedule = new Schedule();
$schedule->addTask(server(8081));
$schedule->run();

