<?php

namespace Corouser\Scheduler;

/**
 * Class: NewTaskTraitTest
 *
 * @author Vsevolod Dolgopolov <zavalit@gmail.com>
 */
class NewTaskTraitTest extends \PHPUnit_Framework_TestCase
{
    use \Corouser\Scheduler\NewTaskTrait;
    
    /**
     * testNewTask
     *
     * @return void
     */
    public function testNewTask()
    {
        $this->assertInstanceOf('Corouser\Scheduler\SystemCall', self::newTask(test_new_task_coroutine()));
    }
}

/**
 * test_new_task_coroutine
 * @return \Generator
 */
function test_new_task_coroutine()
{
    yield;
}
