<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Domain\Scheduler\RecipeJobSchedule;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RunRecipe;
use Peon\UseCase\ScheduleRecipesHandler;
use Peon\UseCase\ScheduleRecipes;
use PHPUnit\Framework\TestCase;

final class ScheduleRecipesHandlerTest extends TestCase
{
    public function testRunRecipesCommandsWillBeDispatched(): void
    {
        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(RunRecipe::class));

        $taskJobSchedule = new RecipeJobSchedule(
            new ProjectId(''),
            RecipeName::SWITCH_TO_MATCH,
            null,
        );
        $getRecipeSchedules = $this->createMock(GetRecipeSchedules::class);
        $getRecipeSchedules->expects(self::once())
            ->method('all')
            ->willReturn([
                $taskJobSchedule,
                $taskJobSchedule,
            ]);

        $shouldSchedule = $this->createMock(ShouldSchedule::class);
        $shouldSchedule->expects(self::exactly(2))
            ->method('cronExpressionNow')
            ->willReturnOnConsecutiveCalls(true, false);

        $handler = new ScheduleRecipesHandler(
            $commandBus,
            $getRecipeSchedules,
            $shouldSchedule,
        );

        $command = new ScheduleRecipes();
        $handler->__invoke($command);
    }
}
