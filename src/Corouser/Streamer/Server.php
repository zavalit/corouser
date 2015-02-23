<?php

namespace Corouser\Streamer;

use Corouser\Scheduler\Schedule;

/**
 * Entryclass for a server functionality
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class Server
{
    use \Corouser\Scheduler\NewTaskTrait;

    /**
     * runs the server
     *
     * @param int $port port
     * @return void
     */
    public function __invoke($port = 8081)
    {
        $task_queue = new \SplQueue();
        $schedule   = new Schedule($task_queue);
        $schedule->addTask($this->boot($port));
        
        $stream = new StreamSchedule($schedule);
        $stream->run();
    }

    /**
     * boot the server and wait for incoming connections
     *
     * @param mixed $port port
     * @return \Generator
     */
    protected function boot($port)
    {
        echo "Starting localhost server on port:$port";

        $socket = @stream_socket_server("tcp://0.0.0.0:$port", $errNo, $errStr);
        if (!$socket) {
            throw new \Exception($errStr, $errNo);
        }

        stream_set_blocking($socket, 0);

        $coSocket = new CoSocket($socket);

        while (true) {
            yield self::newTask($this->handleClient(yield $coSocket->accept()));
        }
    }

    /**
     * handle the incoming connection
     *
     * @param CoSocket $socket client socket
     * @return \Generator
     */
    protected function handleClient(CoSocket $socket)
    {
        $data = (yield $socket->read(8192));

        $msg       = "Recieved following request:".PHP_EOL.PHP_EOL.$data;
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
