<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Task;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class TaskId
{
    public function __construct(public string $id)
    {}
}
