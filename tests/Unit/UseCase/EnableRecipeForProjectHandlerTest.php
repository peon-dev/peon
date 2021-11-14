<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\RecipeAlreadyEnabled;
use PHPMate\Domain\Cookbook\RecipeNotFound;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\UseCase\EnableRecipeForProject;
use PHPMate\UseCase\EnableRecipeForProjectHandler;
use PHPUnit\Framework\TestCase;

class EnableRecipeForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeEnabled(): void
    {
        $command = new EnableRecipeForProject();
        $handler = new EnableRecipeForProjectHandler();

        $projectSpy = $this->createMock(Project::class);
        $projectSpy->expects(self::once())
            ->method('enableRecipe');

        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);
    }


    public function testRecipeAlreadyEnabled(): void
    {
        $this->expectException(RecipeAlreadyEnabled::class);
    }
}
