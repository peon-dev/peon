<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ReadProject
{
    public function __construct(
        public string $projectId,
        public string $name,
        public int $tasksCount,
        public int $jobsCount,
        public int $recipesCount,
    ) {}
}
