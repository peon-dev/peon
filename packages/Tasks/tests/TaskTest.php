<?php
declare(strict_types=1);

namespace PHPMate\Tasks\Tests;

use PHPMate\Tasks\Task;
use PHPMate\Tasks\TaskCanNotHaveNoScripts;
use PHPMate\Tasks\TaskId;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testNoScriptsWillThrowException(): void
    {
        $this->expectException(TaskCanNotHaveNoScripts::class);

        new Task(
            new TaskId(''),
            '',
            []
        );
    }


    public function testRedefineWithNoScriptsWillThrowException(): void
    {
        $this->expectException(TaskCanNotHaveNoScripts::class);

        $task = new Task(new TaskId(''),'', ['']);

        $task->redefine('', []);
    }
}
