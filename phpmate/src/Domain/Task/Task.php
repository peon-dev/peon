<?php

declare(strict_types=1);

namespace PHPMate\Domain\Task;

class Task
{
    private TaskId $taskId;

    private string $name;

    /**
     * @var array<string>
     */
    private array $scripts;


    private function __construct() {}


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    public static function define(
        TaskId $taskId,
        string $name,
        array $scripts
    ): self
    {
        $task = new self();
        $task->taskId = $taskId;
        $task->changeDefinition($name, $scripts);

        return $task;
    }


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    public function changeDefinition(string $name, array $scripts): void
    {
        $this->checkThereAreSomeScripts($scripts);

        $this->name = $name;
        $this->scripts = $scripts;
    }


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    private function checkThereAreSomeScripts(array $scripts): void
    {
        if (count($scripts) <= 0) {
            throw new TaskCanNotHaveNoScripts();
        }
    }
}
