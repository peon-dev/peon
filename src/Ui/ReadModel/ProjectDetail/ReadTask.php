<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use JetBrains\PhpStorm\Immutable;
use Nette\Utils\Json;

#[Immutable]
final class ReadTask
{
    public function __construct(
        public string $taskId,
        public string $name,
        public ?string $schedule,
        public string $commands,
        public ?string $lastJobId,
        public ?\DateTimeImmutable $lastJobStartedAt,
        public ?\DateTimeImmutable $lastJobSucceededAt,
        public ?\DateTimeImmutable $lastJobFailedAt,
    ) {}


    public function getCommandsWithNewLines(): string
    {
        $commandsArray = Json::decode($this->commands);

        return implode('"\n', $commandsArray);
    }
}
