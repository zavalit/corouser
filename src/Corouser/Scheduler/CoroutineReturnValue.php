<?php

namespace Corouser\Scheduler;

/**
 * CoroutineReturnValue wraps a value in order
 * provide a return value for a statcked coroutine logic
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class CoroutineReturnValue
{
    
    /**
     * wrapped value
     *
     * @var mixed
     */
    protected $value;

    /**
     * init class
     *
     * @param mixed $value any value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * get wrapped value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
