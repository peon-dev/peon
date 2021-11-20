<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Task\Value\TaskId;

#[Immutable]
final class RemoveTask
{
    public function __construct(
        public TaskId $taskId
    ) {}
}
