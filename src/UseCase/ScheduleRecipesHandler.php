<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use Lcobucci\Clock\Clock;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Packages\MessageBus\Command\CommandBus;

final class ScheduleRecipesHandler
{
    public function __construct(
        private CommandBus $commandBus,
        private GetRecipeSchedules $getRecipeSchedules,
        private Clock $clock,
    ) {}


    /**
     * @throws \Throwable
     */
    public function __invoke(ScheduleRecipes $command): void
    {
        $schedules = $this->getRecipeSchedules->get();
        $now = $this->clock->now();

        foreach ($schedules as $schedule) {
            if ($schedule->lastTimeScheduledAt !== null) {
                // TODO: this is static for now to run every 8 hours but should not be!!
                $cron = new CronExpression('0 */8 * * *');
                $nextSchedule = $cron->getNextRunDate($schedule->lastTimeScheduledAt);

                if ($nextSchedule > $now) {
                    continue;
                }
            }

            $this->commandBus->dispatch(
                new RunRecipe(
                    $schedule->projectId,
                    $schedule->recipeName,
                )
            );
        }
    }
}
