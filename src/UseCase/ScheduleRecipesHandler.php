<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ScheduleRecipesHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBus         $commandBus,
        private GetRecipeSchedules $getRecipeSchedules,
        private ShouldSchedule     $shouldSchedule,
    ) {}


    /**
     * @throws \Throwable
     */
    public function __invoke(ScheduleRecipes $command): void
    {
        $schedules = $this->getRecipeSchedules->all();

        foreach ($schedules as $schedule) {
            if ($this->shouldSchedule->cronExpressionNow($schedule->cronExpression, $schedule->lastTimeScheduledAt)) {
                $this->commandBus->dispatch(
                    new RunRecipe(
                        $schedule->projectId,
                        $schedule->recipeName,
                    )
                );
            }
        }
    }
}
