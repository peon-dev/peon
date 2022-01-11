<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Task\Value\TaskId;

#[Immutable]
final class RemoveTask
{
    public function __construct(
        public TaskId $taskId
    ) {}
}
