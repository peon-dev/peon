<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

use PHPMate\Domain\Task\Task;
use Symfony\Component\Validator\Constraints\NotBlank;

final class DefineTaskFormData
{
    #[NotBlank]
    public string $name;

    #[NotBlank]
    public string $commands;

    /**
     * @return array<string>
     */
    public function getCommandsAsArray(): array
    {
        $commands = explode("\n", $this->commands);

        array_walk($commands, static function (string &$command) {
            $command = trim($command);
        });

        return array_values(array_filter($commands));
    }


    public static function fromTask(Task $task): self
    {
        $data = new self();
        $data->name = $task->name;
        $data->commands = implode("\n", $task->commands);

        return $data;
    }
}
