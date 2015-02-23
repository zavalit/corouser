<?php

namespace Corouser\Scheduler;

/**
 * Task Object wraps a coroutine and provides an api for it
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class Task implements TaskInterface
{
    use \Corouser\Scheduler\StackedCoroutineTrait;

    /**
     * task id
     *
     * @var int
     */
    protected $task_id;

    /**
     * coroutine
     *
     * @var \Generator
     */
    protected $coroutine;

    /**
     * value to send to coroutine
     *
     * @var mixed
     */
    protected $send_value;

    /**
     * flag for task running state
     *
     * @var bool
     */
    protected $first_run = true;

    /**
     * coroutine exception
     *
     * @var \Exception
     */
    protected $exception = null;

    /**
     * init Task
     *
     * @param int        $task_id   task id
     * @param \Generator $coroutine coroutine
     * @return void
     */
    public function __construct($task_id, \Generator $coroutine)
    {
        $this->task_id = $task_id;

        $this->coroutine = self::stackedCoroutine($coroutine);
    }

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * @param mixed $send_value value that coroutine should send
     * @return void
     */
    public function setSendValue($send_value)
    {
        $this->send_value = $send_value;
    }

    /**
     * @param \Exception $exc coroutine exception
     * @return void
     */
    public function setException(\Exception $exc)
    {
        $this->exception = $exc;
    }

    /**
     * run coroutine
     * @return mixed
     */
    public function run()
    {
        if ($this->first_run) {
            $this->first_run = false;

            return $this->coroutine->current();
        }

        if ($this->exception instanceof \Exception) {
            $retval = $this->coroutine->throw($this->exception);

            $this->exception = null;

            return $retval;
        }

        $retval = $this->coroutine->send($this->send_value);

        $this->send_value = null;

        return $retval;
    }

    /**
     * checks whether a coroutine is empty
     * @return bool
     */
    public function isFinished()
    {
        return !$this->coroutine->valid();
    }
}
