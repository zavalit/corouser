<?php

namespace Corouser\Scheduler;

/**
 * Trait to escape the retval function out of global namespace
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
trait RetValTrait
{
    /**
     * brings a CoroutineReturnValue wrapper for a $value
     *
     * @param mixed $value any value
     * @return CoroutineReturnValue
     */
    private static function retval($value)
    {
        return new \Corouser\Scheduler\CoroutineReturnValue($value);
    }
}
