<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

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
}
