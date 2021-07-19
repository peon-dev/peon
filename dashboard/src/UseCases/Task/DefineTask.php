<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\UseCases\Task;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Worker\Domain\Task\TaskId;

#[Immutable]
final class DefineTask
{
    /**
     * @param array<string> $scripts
     */
    public function __construct(public TaskId $taskId, public string $name, public array $scripts)
    {
    }
}
