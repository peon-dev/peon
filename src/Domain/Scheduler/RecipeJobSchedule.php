<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;

final class RecipeJobSchedule
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName,
        public readonly DateTimeImmutable|null $lastTimeScheduledAt,
        // TODO: should not be static:
        public readonly CronExpression $cronExpression = new CronExpression('0 */8 * * *'),
    ) {}
}
