<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Project\Value\ProjectId;

#[Immutable]
final class DefineTask
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public ProjectId $projectId,
        public string $name,
        public array $commands,
        public ?CronExpression $schedule
    ) {}
}
