<?php

declare(strict_types=1);

namespace PHPMate\Tasks;

class Task
{
    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    public function __construct(
        private TaskId $taskId,
        private string $name,
        private array $scripts
    ) {
        $this->checkThereAreSomeScripts($scripts);
    }


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    public function redefine(string $name, array $scripts): void
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
