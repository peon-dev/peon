<?php
declare(strict_types=1);

namespace PHPMate\Domain\Task\Tests;

use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskCanNotHaveNoScripts;
use PHPMate\Domain\Task\TaskId;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testNoScriptsWillThrowException(): void
    {
        $this->expectException(TaskCanNotHaveNoScripts::class);

        Task::define(
            new TaskId(''),
            '',
            []
        );
    }


    public function testRedefineWithNoScriptsWillThrowException(): void
    {
        $this->expectException(TaskCanNotHaveNoScripts::class);

        $task = Task::define(new TaskId(''),'', ['']);

        $task->changeDefinition('', []);
    }
}
