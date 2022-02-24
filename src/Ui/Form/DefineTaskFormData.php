<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Cron\CronExpression;
use Peon\Domain\Task\Exception\InvalidCronExpression;
use Peon\Domain\Task\Task;
use Symfony\Component\Validator\Constraints\NotBlank;

final class DefineTaskFormData
{
    #[NotBlank]
    public string $name;

    #[NotBlank]
    public string $commands;

    public bool $mergeAutomatically;

    private ?CronExpression $schedule = null;

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


    /**
     * @throws InvalidCronExpression
     */
    public function setSchedule(?string $value): void
    {
        if ($value === '' || $value === null) {
            $this->schedule = null;
            return;
        }

        try {
            $this->schedule = new CronExpression($value);
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidCronExpression($exception->getMessage(), previous: $exception);
        }
    }


    public function getSchedule(): ?CronExpression
    {
        return $this->schedule;
    }


    public static function fromTask(Task $task): self
    {
        $data = new self();
        $data->name = $task->name;
        $data->commands = implode("\n", $task->commands);
        $data->schedule = $task->schedule;
        $data->mergeAutomatically = $task->mergeAutomatically;

        return $data;
    }
}
