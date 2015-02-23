<?php

namespace Corouser\Streamer;

/**
 * Coroutine to manage socket logic
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class CoSocket
{
    use \Corouser\Scheduler\RetValTrait;
    use \Corouser\Streamer\WaitSocketsTrait;

    /**
     * socket
     *
     * @var resource
     */
    protected $socket;

    /**
     * @param resource $socket socket rosource
     * @return void
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    /**
     * fill a scheduler client with wait for read sockets and their tasks
     * and creates a client socket afterwords
     *
     * @return \Genarator
     * through retval and stackedCorutine function returns this function an CoSocket object
     */
    public function accept()
    {
        yield self::waitForRead($this->socket);
        yield self::retval(new CoSocket(stream_socket_accept($this->socket, 0)));
    }

    /**
     * fill a scheduler client with wait for write sockets and thier tasks
     * and then read from that sockets
     *
     * @param int $size reads up to $size bytes from $this->socket
     * @return \Generator
     * through retval and stackedCorutine functions returns this method a string resulted out of fread
     */
    public function read($size)
    {
        yield self::waitForRead($this->socket);
        yield self::retval(fread($this->socket, $size));
    }

    /**
     * write data to socket
     *
     * @param mixed $string data to write
     * @return \Genarator
     */
    public function write($string)
    {
        yield self::waitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    /**
     * close socket
     * @return void
     */
    public function close()
    {
        @fclose($this->socket);
    }
}
