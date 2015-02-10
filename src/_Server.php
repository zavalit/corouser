<?php

use \Schedule;
use \Task;
use \SystemCall;
use \CoroutineReturnValue;
use \CoSocket;

class Server
{

  public function __invoke($port=8081)
  {
     $schedule = new Schedule();
     $schedule->addTask($this->boot($port));
     $schedule->run();
  }

  protected function boot($port)
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
         $this->handleClient(yield $coSocket->accept())
       );   
     }
  
  }

  protected function handleClient(CoSocket $socket)
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

}


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

function retval($value)
{
  return new CoroutineReturnValue($value);
}


function stackedCoroutine(Generator $gen)
{
  $stack = new SplStack();
  for(;;)
  {
    $value = $gen->current();
    
    // nested generator/coroutine
    if($value instanceof Generator){
      $stack->push($gen);
      $gen = $value;
      continue;
    }
    
    // coroutine end or value is a value object instance 
    if(!$gen->valid() || $value instanceof CoroutineReturnValue)
    {
      // if till this point, there are no coroutines in a stack thatn stop here
      if($stack->isEmpty()){
        return;
      }

      $gen = $stack->pop();
      $value = ($value instanceof CoroutineReturnValue)?$value->getValue():NULL;
      $gen->send($value);
      
      continue;

    }

    $gen->send(yield $gen->key() => $value);

  }

}
