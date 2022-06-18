<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use JetBrains\PhpStorm\Immutable;

final class ReadProject
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $name,
        public readonly int $tasksCount,
        public readonly int $jobsCount,
        public readonly int $recipesCount,
    ) {}
}
