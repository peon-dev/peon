<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Task\TaskId;

#[Immutable]
final class RedefineTaskCommand
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public TaskId $taskId,
        public string $name,
        public array $commands
    ) {}
}
