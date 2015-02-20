<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\Schedule;
use Corouser\Scheduler\Task;
use Corouser\Scheduler\SystemCall;
use Corouser\Scheduler\CoroutineReturnValue;

use Corouser\Streamer\CoSocket;
use Corouser\Streamer\StreamSchedule;

class Server
{

  public function __invoke($port=8081)
  {
    $task_queue = new \SplQueue();
    $schedule = new Schedule($task_queue);
    $schedule->addTask($this->boot($port));
    $stream = new StreamSchedule($schedule);
    $stream->run();
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


function newTask(\Generator $coroutine)
{
  return new SystemCall(function(Task $task, Schedule $schedule) use ($coroutine){
    $task->setSendValue($schedule->addTask($coroutine));
    $schedule->scheduleTask($task);
  });
}



