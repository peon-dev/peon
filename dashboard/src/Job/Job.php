<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Job;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class Job
{
    public function __construct(
        public string $status
    )
    {
    }
}
