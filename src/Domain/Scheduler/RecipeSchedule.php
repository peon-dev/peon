<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

use DateTimeInterface;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;

final class RecipeSchedule
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName,
        public readonly DateTimeInterface|null $lastTimeScheduledAt,
    ) {}
}
